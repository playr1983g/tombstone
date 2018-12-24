<?php

namespace Scheb\Tombstone\Analyzer\Report\Html\Renderer;

use Scheb\Tombstone\Analyzer\AnalyzerFileResult;
use Scheb\Tombstone\Analyzer\AnalyzerResult;
use Scheb\Tombstone\Analyzer\Cli\Application;
use Scheb\Tombstone\Analyzer\Report\Html\TemplateFactory;
use Scheb\Tombstone\Analyzer\Report\ReportGeneratorInterface;
use Scheb\Tombstone\Tombstone;
use Scheb\Tombstone\Tracing\PathNormalizer;

class FileRenderer implements ReportGeneratorInterface
{
    /**
     * @var string
     */
    private $reportDir;

    /**
     * @var string
     */
    private $sourceDir;

    /**
     * @var \Text_Template
     */
    private $fileTemplate;

    /**
     * @var \Text_Template
     */
    private $tombstoneTemplate;

    public function __construct(string $reportDir, string $sourceDir)
    {
        $this->reportDir = $reportDir;
        $this->sourceDir = $sourceDir;
        $this->fileTemplate = TemplateFactory::getTemplate('file.html');
        $this->tombstoneTemplate = TemplateFactory::getTemplate('file_tombstone.html');
    }

    public function generate(AnalyzerResult $result): void
    {
        foreach ($result->getPerFile() as $file => $fileResult) {
            if ($fileResult->getDeadCount() || $fileResult->getUndeadCount()) {
                $this->renderFile($fileResult);
            }
        }
    }

    private function renderFile(AnalyzerFileResult $fileResult): void
    {
        $tombstonesList = $this->renderTombstonesList($fileResult);
        $sourceCode = $this->formatSourceCode($fileResult);
        $relativeFilePath = PathNormalizer::makeRelativeTo($fileResult->getFile(), $this->sourceDir);
        $this->fileTemplate->setVar([
            'path_to_root' => './'.str_repeat('../', substr_count($relativeFilePath, '/')),
            'full_path' => $fileResult->getFile(),
            'breadcrumb' => $this->renderBreadcrumb($relativeFilePath),
            'tombstones_list' => $tombstonesList,
            'source_code' => $sourceCode,
            'date' => date('r'),
            'version' => Application::VERSION,
        ]);

        $reportFile = $this->reportDir.DIRECTORY_SEPARATOR.$relativeFilePath.'.html';
        $reportDir = dirname($reportFile);
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0777, true);
        }
        $this->fileTemplate->renderTo($reportFile);
    }

    private function renderTombstonesList(AnalyzerFileResult $fileResult): string
    {
        $tombstoneList = [];

        /** @var Tombstone[] $renderTombstones */
        $renderTombstones = array_merge($fileResult->getDead(), $fileResult->getUndead());
        foreach ($renderTombstones as $tombstone) {
            if (!isset($tombstoneList[$tombstone->getLine()])) {
                $tombstoneList[$tombstone->getLine()] = '';
            }
            $tombstoneList[$tombstone->getLine()] .= $this->renderTombstoneItem(
                $tombstone,
                $tombstone->hasVampires() ? 'danger' : 'success'
            );
        }
        ksort($tombstoneList);

        return implode($tombstoneList);
    }

    private function renderTombstoneItem(Tombstone $tombstone, string $class): string
    {
        $this->tombstoneTemplate->setVar([
            'tombstone' => (string) $tombstone,
            'line' => $tombstone->getLine(),
            'method' => $tombstone->getMethod(),
            'level' => $class,
        ]);

        return $this->tombstoneTemplate->render();
    }

    private function formatSourceCode(AnalyzerFileResult $fileResult): string
    {
        $deadLines = [];
        $undeadLines = [];
        foreach ($fileResult->getDead() as $tombstone) {
            $deadLines[] = $tombstone->getLine();
        }
        foreach ($fileResult->getUndead() as $tombstone) {
            $undeadLines[] = $tombstone->getLine();
        }

        $formattedCode = '';
        $i = 0;
        $code = PhpFileFormatter::loadFile($fileResult->getFile());
        $lineTemplate = '<tr class="%s"><td class="number"><div align="right"><a name="%d"></a><a href="#%d">%d</a></div></td><td class="codeLine">%s</td></tr>';
        foreach ($code as $codeLine) {
            ++$i;

            $class = 'default';
            if (in_array($i, $undeadLines)) {
                $class = 'danger icon-vampire';
            } elseif (in_array($i, $deadLines)) {
                $class = 'success icon-cross';
            }

            $formattedCode .= sprintf($lineTemplate, $class, $i, $i, $i, $codeLine);
        }

        return $formattedCode;
    }

    private function renderBreadcrumb(string $relativeFilePath): string
    {
        $parts = explode('/', $relativeFilePath);
        $numParts = count($parts);
        $breadcrumbString = '<li><a href="./'.str_repeat('../', $numParts - 1).'index.html">'.$this->sourceDir.'</a></li> ';

        $folderUp = $numParts - 2;
        while ($label = array_shift($parts)) {
            if (!$parts) {
                $breadcrumbString .= '<li class="active">'.$label.'</li> ';
            } else {
                $link = './'.str_repeat('../', $folderUp).'index.html';
                $breadcrumbString .= sprintf('<li><a href="%s">%s</a></li> ', $link, $label);
            }
            --$folderUp;
        }

        return $breadcrumbString;
    }
}
