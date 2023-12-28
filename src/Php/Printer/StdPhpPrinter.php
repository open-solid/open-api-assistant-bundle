<?php

namespace OpenSolid\OpenApiAssistantBundle\Php\Printer;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

final readonly class StdPhpPrinter
{
    private Standard $printer;

    public function __construct()
    {
        $this->printer = new Standard(['shortArraySyntax' => true]);
    }

    /**
     * Pretty prints a file of statements (includes the opening <?php tag if it is required).
     *
     * @param Node[] $stmts Array of statements
     *
     * @return string Pretty printed statements
     */
    public function prettyPrintFile(array $stmts): string
    {
        $output = $this->printer->prettyPrintFile($stmts);
        $output = str_replace(') : ', '): ', $output);

        return $output;
    }
}
