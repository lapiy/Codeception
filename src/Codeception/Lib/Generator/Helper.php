<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Helper
{
    use Namespaces;

    protected string $template = <<<EOF
<?php

declare(strict_types=1);
{{namespace}}
// here you can define custom actions
// all public methods declared in helper class will be available in \$I

class {{name}} extends \\Codeception\\Module
{

}

EOF;

    public function __construct(protected string $name, protected string $namespace = '')
    {
    }

    public function produce(): string
    {
        return (new Template($this->template))
            ->place('namespace', $this->getNamespaceHeader($this->namespace . '\\Helper\\' . $this->name))
            ->place('name', $this->getShortClassName($this->name))
            ->produce();
    }

    public function getHelperName(): string
    {
        return rtrim('\\' . $this->namespace, '\\') . '\\Helper\\' . $this->name;
    }
}
