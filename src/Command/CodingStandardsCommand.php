<?php

declare(strict_types=1);

namespace KerrialNewham\Penknife\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'setup:code-quality', description: 'Configure coding standards tools')]
class CodingStandardsCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectRootPath = getcwd(); // Get the current working directory (project root)

        // Check and install tools using the project root path
        $this->checkAndInstallTool($io, "$projectRootPath/vendor/bin/phpstan", 'phpstan/phpstan');
        $this->checkAndInstallTool($io, "$projectRootPath/vendor/bin/ecs", 'symplify/easy-coding-standard');
        $this->checkAndInstallTool($io, "$projectRootPath/vendor/bin/rector", 'rector/rector');

        $this->checkEditorConfig($io);

        $io->success('Code quality tools are set up!');

        return Command::SUCCESS;
    }

    private function checkAndInstallTool(SymfonyStyle $io, string $tool, string $composerPackage): void
    {
        if (!$this->isToolInstalled($tool)) {
            $io->warning("$composerPackage is not installed. Installing...");
            $this->installTool($io, $composerPackage);
        } else {
            $io->info("$composerPackage is already installed.");
        }
    }

    private function isToolInstalled(string $tool): bool
    {
        return file_exists($tool) || $this->isGloballyInstalled($tool);
    }

    private function isGloballyInstalled(string $tool): bool
    {
        $process = Process::fromShellCommandline("which $tool");
        $process->run();

        return $process->isSuccessful();
    }

    private function installTool(SymfonyStyle $io, string $composerPackage): void
    {
        $io->info("Running: composer require $composerPackage");

        // Run composer to install the package
        $process = Process::fromShellCommandline("composer require $composerPackage");
        $process->run(function ($type, $buffer) use ($io) {
            $io->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $io->error("Failed to install $composerPackage");
        }
    }

    private function checkEditorConfig(SymfonyStyle $io): void
    {
        $editorConfigPath = getcwd() . '/.editorconfig';
        $defaultConfigPath = __DIR__ . '/../defaults/.editorconfig'; // Adjust this path if needed

        if (file_exists($editorConfigPath)) {
            $io->info('.editorconfig file already exists.');
        } else {
            $io->warning('.editorconfig file is missing. Copying default configuration...');
            $this->copyDefaultEditorConfig($defaultConfigPath, $editorConfigPath, $io);
        }
    }

    private function copyDefaultEditorConfig(string $defaultPath, string $projectPath, SymfonyStyle $io): void
    {
        if (copy($defaultPath, $projectPath)) {
            $io->info('Default .editorconfig file copied to the project.');
        } else {
            $io->error('Failed to copy .editorconfig file.');
        }
    }
}

