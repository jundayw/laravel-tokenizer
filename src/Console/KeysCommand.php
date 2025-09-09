<?php

namespace Jundayw\Tokenizer\Console;

use Illuminate\Console\Command;
use Jundayw\Tokenizer\Tokenizer;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

#[AsCommand(name: 'tokenizer:keys')]
class KeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokenizer:keys
                                      {algo=RS256 : Algorithm to generate keys (RS256, RS384, RS512, ES256, ES384, ES512, EdDSA)}
                                      {--f|force : Overwrite keys they already exist}
                                      {--l|length=2048 : Length of the private key for RSA keys}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the encryption keys for API authentication';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        [$publicKey, $privateKey] = [
            Tokenizer::keyPath(config('tokenizer.drivers.jwt.public_key', 'tokenizer-public.key')),
            Tokenizer::keyPath(config('tokenizer.drivers.jwt.private_key', 'tokenizer-private.key')),
        ];

        if ((file_exists($publicKey) || file_exists($privateKey)) && !$this->option('force')) {
            $this->components->error('Encryption keys already exist. Use the --force option to overwrite them.');
            return 1;
        }

        $algo    = $this->argument('algo');
        $keySize = $this->hasOption('length') ? intval($this->option('length')) : 4096;

        try {
            $config = match (true) {
                preg_match('/^RS(\d{3})$/', $algo, $matches) || $matches => [
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                    'private_key_bits' => max(intval($matches[1]) * 8, $keySize),
                ],
                preg_match('/^ES(\d{3})$/', $algo, $matches) || $matches => [
                    'private_key_type' => OPENSSL_KEYTYPE_EC,
                    'curve_name'       => match (intval($matches[1])) {
                        256 => 'prime256v1',
                        384 => 'secp384r1',
                        512 => 'secp521r1',
                        default => throw new RuntimeException("Unsupported ES curve: {$matches[1]}"),
                    },
                ],
                $algo === 'EdDSA' => match (true) {
                    defined('OPENSSL_KEYTYPE_ED25519') => ['private_key_type' => OPENSSL_KEYTYPE_ED25519],
                    default => throw new RuntimeException('EdDSA not supported: PHP or OpenSSL too old'),
                },
                default => throw new RuntimeException("Unsupported algorithm: {$algo}"),
            };

            $resource = openssl_pkey_new($config);
            if ($resource === false) {
                throw new RuntimeException('[OpenSSL Error] Failed to generate RSA key pair.');
            }

            $export = openssl_pkey_export($resource, $pkey);
            if ($export === false) {
                throw new RuntimeException('[OpenSSL Error] key parameter is not a valid private key');
            }

            $details = openssl_pkey_get_details($resource);
            if ($details === false) {
                throw new RuntimeException('[OpenSSL Error] Failed to get key details.');
            }
        } catch (Throwable $throwable) {
            $this->components->error($throwable->getMessage());
            return -1;
        }

        if ($this->storage($publicKey, $details['key']) === false) {
            $this->components->error("Failed to write file: {$publicKey}");
            return false;
        }

        if ($this->storage($privateKey, $pkey) === false) {
            $this->components->error("Failed to write file: {$privateKey}");
            return false;
        }

        if (!windows_os()) {
            chmod($publicKey, 0660);
            chmod($privateKey, 0600);
        }

        $this->components->info('Encryption keys generated successfully.');

        return 0;
    }

    /**
     * Put contents to a file safely.
     *
     * @param string $path
     * @param string $data
     * @param int    $flags
     *
     * @return bool|int
     */
    protected function storage(string $path, string $data, int $flags = 0): bool|int
    {
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            $this->components->error("Failed to create directory: {$dir}");
            return false;
        }

        return file_put_contents($path, $data, $flags);
    }
}
