<?php


use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Tg\SimpleRPC\SimpleRPCServer\CompilerPass\RpcServerCompilerPass;

require __DIR__ . '/vendor/autoload.php';

$container = new ContainerBuilder();

$files = (new \Symfony\Component\Finder\Finder())->in(
    [__DIR__. '/src/SimpleRPCServer/', __DIR__. '/src/SimpleRPCServer/Module/*/']
)->name('services.xml')->files();

/** @var $files \Symfony\Component\Finder\SplFileInfo[] */
foreach($files as $file) {
    (new XmlFileLoader($container, new FileLocator([$file->getPath()])))->load($file->getFilename());
}

$container
    ->addCompilerPass(new RpcServerCompilerPass())
    ->compile()
;

$container->set('container', $container);
$application = new Application();
$application->add($container->get('command_start_angel'));
$application->add($container->get('command_start_server'));
$application->setDefaultCommand('server');
$application->run();
