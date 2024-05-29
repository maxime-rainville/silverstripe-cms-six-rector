<?php

namespace MaximeRainville\SilverstripeCmsSixRector;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class ReplaceSessionConstructorVisitor extends NodeVisitorAbstract
{
    /** @var array<int, array<int, string>|null> */
    private array $typeStack = [];

    public function beforeTraverse(array $nodes): ?array
    {
        // $this->typeStack = [];
        return null;
    }

    public function enterNode(Node $node): ?Node
    {

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        // pop the stack - we're leaving TryCatch and FunctionLike
        // which are the two node types that are pushing items
        // to the stack in enterNode()
        // array_pop($this->typeStack);

        return null;
    }
}
