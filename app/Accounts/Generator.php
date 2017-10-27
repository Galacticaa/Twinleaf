<?php namespace Twinleaf\Accounts;

use Exception;
use Twinleaf\Account;
use Twinleaf\MapArea;
use Twinleaf\Setting;
use Faker\Factory;

class Generator
{
    protected $area;

    protected $config;

    protected $faker;

    protected $symbols = ['?', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', ']', '<', '>'];

    public function __construct(MapArea $area = null)
    {
        $this->area = $area;
        $this->config = Setting::first();
        $this->faker = Factory::create();
    }

    public function generate(int $count = null)
    {
        $accounts = [];
        $nameUses = 25;
        $count = $this->area ? $this->area->accounts_target : ($count ?? 1);

        for ($i = 0; $i < ceil($count / $nameUses); $i++) {
            $batchCount = (($i + 1) * $nameUses) < $count
                        ? $nameUses
                        : $count - $nameUses * $i;

            $accounts = array_merge($accounts, $this->generateBatch($batchCount));
        }

        return $accounts;
    }

    public function generateBatch(int $count)
    {
        $accounts = [];
        $basename = $this->username();

        for ($i = 0; $i < $count; $i++) {
            $accounts[] = $this->generateSingle($basename);
        }

        return $accounts;
    }

    public function generateSingle(string $basename = null)
    {
        $account = new Account();
        $account->area()->associate($this->area);
        $account->domain = $this->randomDomain();
        $account->country = 'GB';
        $account->username = $this->faker->unique()->bothify($basename ?? $this->username());
        $account->password = $this->password();
        $account->birthday = $this->birthday();

        $account->save();

        return $account;
    }

    protected function randomDomain()
    {
        $domains = $this->config->email_domains;

        if (!$domains) {
            throw new Exception("No email domains defined");
        }

        return count($domains) === 1 ? trim($domains[0])
             : trim($domains[array_rand($domains)]);
    }

    protected function username()
    {
        $account = '1234567890987654321';

        while (strlen($account) > 15) {
            $parts = [
                $this->faker->domainWord,
                $this->faker->lastName,
                '##?#',
            ];

            $account = implode('', $parts);
        }

        return $account;
    }

    protected function password()
    {
        $symbols = [];
        while (0 === count(array_intersect($this->symbols, $symbols))) {
            $symbols = $this->faker->randomElements($this->symbols, $this->faker->numberBetween(2, 4));
        }

        $alphanum = strtoupper($this->faker->bothify('???###'));
        $combined = str_pad($alphanum.implode('', $symbols), 12, '?');

        $password = $this->faker->lexify($combined);

        return $this->faker->shuffle($password);
    }

    protected function birthday()
    {
        return $this->faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d');
    }
}
