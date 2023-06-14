<?php
namespace gumphp\dumpserver;

use think\Service;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Server\Connection;
use Symfony\Component\VarDumper\VarDumper;

class DumpServerService extends Service
{
    public function register(): void
    {
        $connection = new Connection(config('dump-server.host', 'tcp://127.0.0.1:9912'), [
            'request' => new RequestContext($this->app->request),
            'source' => new SourceContextProvider('utf-8', base_path()),
        ]);
        VarDumper::setHandler(function ($var) use ($connection) {
            $this->app->make(Dumper::class, ['connection' => $connection])->dump($var);
        });
    }

    public function boot(): void
    {
        $this->commands([
            DumpServerCommand::class,
        ]);
    }
}
