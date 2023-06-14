<?php
namespace gumphp\dumpserver;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Server\DumpServer;
use Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;
use Symfony\Component\VarDumper\Command\Descriptor\HtmlDescriptor;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Logger\ConsoleLogger;

class DumpServerCommand extends Command
{
    protected $server;

    protected function configure()
    {
        // 指令配置
        $this->setName('dump-server')
            ->setDescription('Start the dump server to collect dump information.')
            ->addOption('format', 'f', Option::VALUE_REQUIRED, 'The output format (cli,html).', 'cli');
    }

    protected function execute(Input $input, Output $output)
    {
        $format = $this->input->getOption('format');
        if (!in_array($format, ['cli', 'html'])) {
            return $output->error(sprintf('Unsupported format "%s".', $format));
        }

        $getter = 'get' . ucfirst($format) . 'Descriptor';
        /**
         * @var CliDescriptor|HtmlDescriptor $descriptor
         */
        $descriptor = $this->{$getter}();

        $in = new ArgvInput();
        $out = new ConsoleOutput();
        $io = new SymfonyStyle($in, $out);

        $errorIo = $io->getErrorStyle();
        $errorIo->title('Var Dump Server');

        $this->server = new DumpServer(config('dump-server.host'));
        $this->server->start();

        $errorIo->success(sprintf('Server listening on %s', $this->server->getHost()));
        $errorIo->comment('Quit the server with CONTROL-C.');

        $this->server->listen(function (Data $data, array $context, int $clientId) use ($descriptor, $io) {
            $descriptor->describe($io, $data, $context, $clientId);
        });
    }

    /**
     * @return CliDescriptor
     */
    protected function getCliDescriptor()
    {
        return new CliDescriptor(new CliDumper);
    }

    /**
     * @return HtmlDescriptor
     */
    protected function getHtmlDescriptor()
    {
        return new HtmlDescriptor(new HtmlDumper);
    }
}
