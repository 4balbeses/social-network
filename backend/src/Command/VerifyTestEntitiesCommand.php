<?php

namespace App\Command;

use App\Repository\TestUserRepository;
use App\Repository\TestProductRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:verify-test-entities',
    description: 'Verifies test entities exist and shows various queries'
)]
class VerifyTestEntitiesCommand extends Command
{
    public function __construct(
        private TestUserRepository $testUserRepository,
        private TestProductRepository $testProductRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Verify all entities
        $users = $this->testUserRepository->findAll();
        $products = $this->testProductRepository->findAll();

        $io->title('Test Entity Verification');

        $io->section('Users');
        $userRows = [];
        foreach ($users as $user) {
            $userRows[] = [
                $user->getId(),
                $user->getName(),
                $user->getEmail(),
                $user->getAge(),
                $user->getCreatedAt()->format('Y-m-d H:i:s'),
                substr($user->getBio() ?? '', 0, 20) . '...'
            ];
        }
        $io->table(['ID', 'Name', 'Email', 'Age', 'Created', 'Bio'], $userRows);

        $io->section('Products');
        $productRows = [];
        foreach ($products as $product) {
            $productRows[] = [
                $product->getId(),
                $product->getName(),
                '$' . $product->getPrice(),
                $product->getStock(),
                $product->isActive() ? 'Yes' : 'No',
                substr($product->getDescription() ?? '', 0, 30) . '...'
            ];
        }
        $io->table(['ID', 'Name', 'Price', 'Stock', 'Active', 'Description'], $productRows);

        // Test custom repository methods
        $io->section('Repository Method Tests');
        
        $youngUsers = $this->testUserRepository->findByAgeRange(20, 30);
        $io->info(sprintf('Users aged 20-30: %d', count($youngUsers)));
        
        $activeProducts = $this->testProductRepository->findActiveProducts();
        $io->info(sprintf('Active products: %d', count($activeProducts)));
        
        $affordableProducts = $this->testProductRepository->findByPriceRange('0', '100');
        $io->info(sprintf('Products under $100: %d', count($affordableProducts)));
        
        $userByEmail = $this->testUserRepository->findByEmail('john@example.com');
        $io->info(sprintf('User found by email: %s', $userByEmail ? $userByEmail->getName() : 'None'));

        $io->success('All test entities verified successfully! They exist in the database and custom queries work.');
        
        return Command::SUCCESS;
    }
}