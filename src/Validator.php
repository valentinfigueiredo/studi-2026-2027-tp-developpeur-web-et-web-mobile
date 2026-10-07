<?php

declare(strict_types=1);

namespace App;

/**
 * Validation des saisies côté serveur.
 * Les contrôles HTML/JS côté navigateur sont du confort : ils se contournent facilement.
 * Seule la validation côté serveur fait foi.
 */
final class Validator
{
    /** RG1 : 10 caractères minimum, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial. */
    public const PASSWORD_REGEX = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{10,}$/';

    private array $errors = [];

    public function required(string $field, mixed $value, string $label): self
    {
        if (trim((string) $value) === '') {
            $this->errors[$field] = "Le champ « $label » est obligatoire.";
        }
        return $this;
    }

    public function email(string $field, string $value): self
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "L'adresse email n'est pas valide.";
        }
        return $this;
    }

    public function password(string $field, string $value): self
    {
        if (!preg_match(self::PASSWORD_REGEX, $value)) {
            $this->errors[$field] = 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, '
                . 'une minuscule, un chiffre et un caractère spécial.';
        }
        return $this;
    }

    public function phone(string $field, string $value): self
    {
        if ($value !== '' && !preg_match('/^(?:\+33|0)[1-9]\d{8}$/', self::normalizePhone($value))) {
            $this->errors[$field] = 'Le numéro de téléphone doit être un numéro français valide (ex : 06 12 34 56 78).';
        }
        return $this;
    }

    public function maxLength(string $field, string $value, int $max, string $label): self
    {
        if (mb_strlen($value) > $max) {
            $this->errors[$field] = "Le champ « $label » ne doit pas dépasser $max caractères.";
        }
        return $this;
    }

    public function intRange(string $field, mixed $value, int $min, ?int $max, string $message): self
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        if ($int === false || $int < $min || ($max !== null && $int > $max)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function date(string $field, string $value, string $label): self
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            $this->errors[$field] = "Le champ « $label » doit être une date valide.";
        }
        return $this;
    }

    public function time(string $field, string $value, string $label): self
    {
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value)) {
            $this->errors[$field] = "Le champ « $label » doit être une heure valide (HH:MM).";
        }
        return $this;
    }

    public function in(string $field, mixed $value, array $allowed, string $message): self
    {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function custom(string $field, bool $condition, string $message): self
    {
        if (!$condition && !isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/[\s.\-]/', '', $phone) ?? '';
    }
}
