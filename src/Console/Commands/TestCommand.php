<?php

namespace Twoavy\EvaluationTool\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluation:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluation tool test command';

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
     * @return int
     */
    public function handle()
    {
        echo "Evaluation tool test successful";
        return 0;
    }
}
