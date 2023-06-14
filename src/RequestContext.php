<?php
namespace gumphp\dumpserver;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use think\Request;

class RequestContext implements ContextProviderInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var VarCloner
     */
    protected $cloner;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->cloner = new VarCloner();
        $this->cloner->setMaxItems(0);
    }

    public function getContext(): ?array
    {
        if (null === $this->request) {
            return null;
        }

        return [
            'uri' => $this->request->url(),
            'method' => $this->request->method(),
            'controller' => $this->request->controller(),
            'action' => $this->request->action(),
            'identifier' => spl_object_hash($this->request),
        ];
    }
}