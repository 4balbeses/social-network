<?php

// Simple bootstrap for testing just entity creation logic without Symfony dependencies

// Manually include the required PHP files
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Mock Doctrine Collection interface
interface DoctrineColl extends \Countable, \IteratorAggregate
{
    public function contains($element): bool;
    public function add($element): void;
    public function removeElement($element): bool;
}

// Mock Doctrine classes that are needed
class ArrayCollection implements DoctrineColl
{
    private array $elements;
    
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }
    
    public function contains($element): bool
    {
        return in_array($element, $this->elements, true);
    }
    
    public function add($element): void
    {
        $this->elements[] = $element;
    }
    
    public function removeElement($element): bool
    {
        $key = array_search($element, $this->elements, true);
        if ($key !== false) {
            unset($this->elements[$key]);
            return true;
        }
        return false;
    }
    
    public function count(): int
    {
        return count($this->elements);
    }
    
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }
}

// Create namespace for Doctrine Collections
if (!class_exists('Doctrine\Common\Collections\ArrayCollection')) {
    class_alias('ArrayCollection', 'Doctrine\Common\Collections\ArrayCollection');
}

if (!interface_exists('Doctrine\Common\Collections\Collection')) {
    class_alias('DoctrineColl', 'Doctrine\Common\Collections\Collection');
}

// Mock API Platform and Symfony classes
if (!class_exists('ApiPlatform\Metadata\ApiResource')) {
    class ApiResourceAttribute { }
    class_alias('ApiResourceAttribute', 'ApiPlatform\Metadata\ApiResource');
}

// Mock Symfony Security interfaces
if (!interface_exists('Symfony\Component\Security\Core\User\UserInterface')) {
    interface UserInterface {
        public function getUserIdentifier(): string;
        public function getRoles(): array;
        public function eraseCredentials();
    }
    class_alias('UserInterface', 'Symfony\Component\Security\Core\User\UserInterface');
}

if (!interface_exists('Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface')) {
    interface PasswordAuthenticatedUserInterface {
        public function getPassword(): ?string;
    }
    class_alias('PasswordAuthenticatedUserInterface', 'Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface');
}

// Mock Doctrine Validation
if (!class_exists('Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity')) {
    class UniqueEntityAttribute { }
    class_alias('UniqueEntityAttribute', 'Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity');
}

// Mock ORM attributes (we'll just ignore them)
if (!class_exists('Doctrine\ORM\Mapping')) {
    class ORMAttribute { 
        public function __construct(...$args) {}
    }
    
    class ORM {
        public static function Entity(...$args) { return new ORMAttribute(); }
        public static function Id(...$args) { return new ORMAttribute(); }
        public static function GeneratedValue(...$args) { return new ORMAttribute(); }
        public static function Column(...$args) { return new ORMAttribute(); }
        public static function ManyToOne(...$args) { return new ORMAttribute(); }
        public static function OneToMany(...$args) { return new ORMAttribute(); }
        public static function JoinColumn(...$args) { return new ORMAttribute(); }
    }
    
    class_alias('ORM', 'Doctrine\ORM\Mapping');
}

if (!class_exists('Doctrine\DBAL\Types\Types')) {
    class Types {
        public const TEXT = 'text';
        public const DATETIME_MUTABLE = 'datetime';
    }
    class_alias('Types', 'Doctrine\DBAL\Types\Types');
}

echo "Simple bootstrap loaded successfully.\n";