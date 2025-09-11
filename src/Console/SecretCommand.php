<?php

namespace Jundayw\Tokenizer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'tokenizer:secret')]
class SecretCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokenizer:secret
                            {--s|always-no : If the key already exists, generation is skipped.}
                            {--d|display : Display the key instead of modifying files.}
                            {--f|force : Skip confirmation when overwriting an existing key.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the secret key used to sign tokens';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $secretKey   = 'TOKEN_SECRET_KEY';
        $secretValue = Str::random(64);

        // Display the key instead of modifying files.
        if ($this->option('display')) {
            return $this->display($secretValue);
        }
        // Check if the configuration file exists
        if (file_exists($path = $this->envPath()) === false) {
            $this->error('The configuration file [.env] does not exist.');
            return 1;
        }
        // Get the Original Secret Key
        if ($original = env($secretKey)) {
            $this->question('Original Secret Keys:');
            $this->display($original);
        }
        // Append if the Secret key does not exist
        if (Str::contains(file_get_contents($path), $secretKey) === false) {
            file_put_contents($path, "{$secretKey}={$secretValue}" . PHP_EOL, FILE_APPEND);
        } else {
            // If there is always-no parameter, skip it
            if ($this->option('always-no')) {
                $this->comment("[{$secretKey}] Skipping...");
                return 0;
            }
            // If there is no force parameter, ask whether to overwrite
            if (!$this->option('force') && $this->confirm("Are you sure you want to override [{$secretKey}]?") === false) {
                $this->comment("Phew... your key hasn't changed at all.");
                return 0;
            }
            // Update the Secret Key
            file_put_contents($path, str_replace(
                "{$secretKey}={$original}",
                "{$secretKey}={$secretValue}",
                file_get_contents($path)
            ));
        }

        $this->question('Current Secret Keys:');
        return $this->display($secretValue);
    }

    /**
     * Display the Secret Key
     *
     * @param string $secretKey
     *
     * @return int
     */
    public function display(string $secretKey): int
    {
        $this->alert($secretKey);
        return 0;
    }

    /**
     * Get the .env file path.
     *
     * @return string
     */
    protected function envPath(): string
    {
        if (method_exists($this->laravel, 'environmentFilePath')) {
            return $this->laravel->environmentFilePath();
        }

        // check if laravel version Less than 5.4.17
        if (version_compare($this->laravel->version(), '5.4.17', '<')) {
            return $this->laravel->basePath() . DIRECTORY_SEPARATOR . '.env';
        }

        return $this->laravel->basePath('.env');
    }
}
