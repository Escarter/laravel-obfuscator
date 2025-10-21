<?php

namespace Escarter\LaravelObfuscator\Services;

use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract};

class ObfuscatorVisitor extends NodeVisitorAbstract
{
    protected array $nameMap;
    protected array $config;
    protected bool $isLivewireComponent = false;
    protected array $compactVars = [];

    public function __construct(array &$nameMap, array $config)
    {
        $this->nameMap = &$nameMap;
        $this->config = $config;
    }

    /**
     * Run before traversing the AST
     */
    public function beforeTraverse(array $nodes): ?array
    {
        // Find variables used in compact() calls
        $finder = new CompactFinder();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($finder);
        $traverser->traverse($nodes);
        $this->compactVars = $finder->getVars();
        
        return null;
    }

    /**
     * Process each node
     */
    public function enterNode(Node $node): ?Node
    {
        // Detect Livewire components
        if ($node instanceof Node\Stmt\Class_ && 
            $node->extends && 
            $node->extends->toString() === 'Component') {
            $this->isLivewireComponent = true;
        }

        // Convert compact() to explicit arrays
        if ($node instanceof Node\Expr\FuncCall && 
            $node->name instanceof Node\Name && 
            $node->name->toString() === 'compact') {
            return $this->convertCompactToArray($node);
        }

        // Obfuscate variable names
        if ($node instanceof Node\Expr\Variable && is_string($node->name)) {
            if ($this->shouldObfuscateVariable($node->name)) {
                $node->name = $this->obfuscateName($node->name);
            }
        }

        // Obfuscate method names
        if ($node instanceof Node\Stmt\ClassMethod) {
            if ($this->shouldObfuscateMethod($node->name->name)) {
                if ($node->isPrivate() || $node->isProtected()) {
                    $node->name->name = $this->obfuscateName($node->name->name);
                }
            }
        }

        // Obfuscate property names
        if ($node instanceof Node\Stmt\Property) {
            if (!($this->isLivewireComponent && $node->isPublic())) {
                foreach ($node->props as $prop) {
                    if ($this->shouldObfuscateProperty($prop->name->name) && $node->isPrivate()) {
                        $prop->name->name = $this->obfuscateName($prop->name->name);
                    }
                }
            }
        }

        return $node;
    }

    /**
     * Convert compact() calls to explicit arrays
     */
    protected function convertCompactToArray(Node\Expr\FuncCall $node): Node\Expr\Array_
    {
        $items = [];
        
        foreach ($node->args as $arg) {
            if ($arg->value instanceof Node\Scalar\String_) {
                $varName = $arg->value->value;
                $items[] = new Node\Expr\ArrayItem(
                    new Node\Expr\Variable($varName),
                    new Node\Scalar\String_($varName)
                );
            }
        }
        
        return new Node\Expr\Array_($items, ['kind' => Node\Expr\Array_::KIND_SHORT]);
    }

    /**
     * Check if a variable should be obfuscated
     */
    protected function shouldObfuscateVariable(string $name): bool
    {
        // Don't obfuscate compact variables
        if (in_array($name, $this->compactVars)) {
            return false;
        }

        // Don't obfuscate protected variables
        if (in_array($name, $this->config['protected_variables'])) {
            return false;
        }

        return true;
    }

    /**
     * Check if a method should be obfuscated
     */
    protected function shouldObfuscateMethod(string $name): bool
    {
        // Don't obfuscate protected methods
        if (in_array($name, $this->config['protected_methods'])) {
            return false;
        }

        // Don't obfuscate Livewire lifecycle hooks (updated*)
        if (preg_match('/^updated[A-Z]/', $name)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a property should be obfuscated
     */
    protected function shouldObfuscateProperty(string $name): bool
    {
        return !in_array($name, $this->config['protected_properties']);
    }

    /**
     * Generate an obfuscated name
     */
    protected function obfuscateName(string $original): string
    {
        if (isset($this->nameMap[$original])) {
            return $this->nameMap[$original];
        }

        if ($this->config['unicode_names']) {
            $obfuscated = $this->generateUnicodeName();
        } else {
            $obfuscated = '_' . bin2hex(random_bytes(4));
        }

        $this->nameMap[$original] = $obfuscated;
        return $obfuscated;
    }

    /**
     * Generate a Unicode-based obfuscated name
     */
    protected function generateUnicodeName(): string
    {
        $unicodeChars = [
            'O', 'o', 'О', 'о', 'Ο', 'ο',
            'I', 'l', 'Ӏ', 'І', 'Ι', 'ι',
            'a', 'а', 'ɑ',
            'e', 'е', 'ԑ',
            'c', 'с', 'ϲ',
            'p', 'р', 'ρ',
            'B', 'В', 'Β',
            'H', 'Н', 'Η',
            'K', 'К', 'Κ',
            'M', 'М', 'Μ',
            'T', 'Т', 'Τ',
            'X', 'Х', 'Χ',
            'Y', 'У', 'Υ',
            'Z', 'Ζ',
        ];

        $name = strtolower($unicodeChars[array_rand($unicodeChars)]);
        
        for ($i = 0; $i < rand(3, 5); $i++) {
            $name .= $unicodeChars[array_rand($unicodeChars)];
        }

        return $name;
    }
}

/**
 * Helper class to find variables used in compact() calls
 */
class CompactFinder extends NodeVisitorAbstract
{
    protected array $vars = [];

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Node\Expr\FuncCall && 
            $node->name instanceof Node\Name && 
            $node->name->toString() === 'compact') {
            
            foreach ($node->args as $arg) {
                if ($arg->value instanceof Node\Scalar\String_) {
                    $this->vars[] = $arg->value->value;
                }
            }
        }
        
        return null;
    }

    public function getVars(): array
    {
        return array_unique($this->vars);
    }
}

