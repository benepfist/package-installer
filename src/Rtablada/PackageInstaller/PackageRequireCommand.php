<?php namespace Rtablada\PackageInstaller;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PackageRequireCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'package:require';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Adds required packages to your composer.json and installs them.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$command = 'composer require ' . $this->argument('packageName');
		$command .= ($this->option('dev')) ? ' --dev' : ''; 
		$command .= ($this->option('dev')) ? ' --no-update' : ''; 
		$command .= ($this->option('dev')) ? ' --no-progress' : ''; 
		passthru('composer require ' . $this->argument('packageName'));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('packageName', InputArgument::REQUIRED, 'Name of the composer package to be installed.'),
		);
	}

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
			array('dev', null, InputOption::VALUE_NONE, 'Add requirement to require-dev.', null),
			array('no-update', null, InputOption::VALUE_NONE, 'Disables the automatic update of the dependiencies.', null),
			array('no-progress', null, InputOption::VALUE_NONE, 'Disables the automatic update of the dependiencies.', null),
		);
    }

}
