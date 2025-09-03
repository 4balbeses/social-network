<?php

namespace App\Command;

use App\Entity\TestUser;
use App\Entity\TestProduct;
use App\Repository\TestUserRepository;
use App\Repository\TestProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-entities',
    description: 'Creates test entities and saves them to the database'
)]
class CreateTestEntitiesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TestUserRepository $testUserRepository,
        private TestProductRepository $testProductRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Create test users
        $testUsers = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'age' => 30, 'bio' => 'Software developer'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'age' => 25, 'bio' => 'Designer'],
            ['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'age' => 35, 'bio' => 'Product manager'],
        ];

        $io->section('Creating Test Users');
        foreach ($testUsers as $userData) {
            $user = new TestUser();
            $user->setName($userData['name'])
                ->setEmail($userData['email'])
                ->setAge($userData['age'])
                ->setBio($userData['bio']);

            $this->testUserRepository->save($user, true);
            $io->success(sprintf('Created user: %s (%s)', $user->getName(), $user->getEmail()));
        }

        // Create test products
        $testProducts = [
            ['name' => 'Laptop', 'price' => '999.99', 'description' => 'High-performance laptop', 'stock' => 10],
            ['name' => 'Mouse', 'price' => '29.99', 'description' => 'Wireless gaming mouse', 'stock' => 50],
            ['name' => 'Keyboard', 'price' => '89.99', 'description' => 'Mechanical keyboard', 'stock' => 25],
            ['name' => 'Monitor', 'price' => '299.99', 'description' => '27-inch 4K monitor', 'stock' => 15],
        ];

        $io->section('Creating Test Products');
        foreach ($testProducts as $productData) {
            $product = new TestProduct();
            $product->setName($productData['name'])
                ->setPrice($productData['price'])
                ->setDescription($productData['description'])
                ->setStock($productData['stock'])
                ->setActive(true);

            $this->testProductRepository->save($product, true);
            $io->success(sprintf('Created product: %s ($%s)', $product->getName(), $product->getPrice()));
        }

        // Verify entities exist
        $io->section('Verification');
        $userCount = count($this->testUserRepository->findAll());
        $productCount = count($this->testProductRepository->findAll());
        
        $io->info(sprintf('Total users in database: %d', $userCount));
        $io->info(sprintf('Total products in database: %d', $productCount));

        if ($userCount > 0 && $productCount > 0) {
            $io->success('Test entities have been successfully created and saved to the database!');
            return Command::SUCCESS;
        } else {
            $io->error('Failed to create test entities.');
            return Command::FAILURE;
        }
    }
}