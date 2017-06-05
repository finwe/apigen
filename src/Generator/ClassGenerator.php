<?php declare(strict_types=1);

namespace ApiGen\Generator;

use ApiGen\Configuration\Configuration;
use ApiGen\Contract\Generator\GeneratorInterface;
use ApiGen\Reflection\Contract\Reflection\Class_\ClassReflectionInterface;
use ApiGen\Reflection\Contract\ReflectionStorageInterface;
use ApiGen\SourceCodeHighlighter\SourceCodeHighlighter;
use ApiGen\Templating\TemplateRenderer;

final class ClassGenerator implements GeneratorInterface
{
    /**
     * @var ReflectionStorageInterface
     */
    private $reflectionStorage;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SourceCodeHighlighter
     */
    private $sourceCodeHighlighter;

    /**
     * @var TemplateRenderer
     */
    private $templateRenderer;

    public function __construct(
        ReflectionStorageInterface $reflectionStorage,
        Configuration $configuration,
        SourceCodeHighlighter $sourceCodeHighlighter,
        TemplateRenderer $templateRenderer
    ) {
        $this->reflectionStorage = $reflectionStorage;
        $this->configuration = $configuration;
        $this->sourceCodeHighlighter = $sourceCodeHighlighter;
        $this->templateRenderer = $templateRenderer;
    }

    public function generate(): void
    {
        foreach ($this->reflectionStorage->getClassReflections() as $classReflection) {
            $this->generateForClass($classReflection);
            $this->generateSourceCodeForClass($classReflection);
        }
    }

    private function generateForClass(ClassReflectionInterface $classReflection): void
    {
        $this->templateRenderer->renderToFile(
            $this->configuration->getTemplateByName('class'),
            $this->configuration->getDestinationWithPrefixName('class-', $classReflection->getName()),
            [
                'activePage' => 'class',
                'class' => $classReflection,
            ]
        );
    }

    private function generateSourceCodeForClass(ClassReflectionInterface $classReflection): void
    {
        $content = file_get_contents($classReflection->getFileName());
        $highlightedContent = $this->sourceCodeHighlighter->highlightAndAddLineNumbers($content);

        $this->templateRenderer->renderToFile(
            $this->configuration->getTemplateByName('source'),
            $this->configuration->getDestinationWithPrefixName('source-class-', $classReflection->getName()),
            [
                'activePage' => 'class',
                'activeClass' => $classReflection,
                'fileName' => $classReflection->getFileName(),
                'source' => $highlightedContent
            ]
        );
    }
}