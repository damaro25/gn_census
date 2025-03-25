<?php

namespace App\Entity;

use App\Repository\PasswordUpdateRepository;

use Symfony\Component\Validator\Constraints as Assert;


class PasswordUpdate
{
    /**
     * @Assert\NotBlank(message="Required fields !")
     */
    private $oldPassword;

    /**
     * @Assert\Length(min=4, minMessage="Your password must be at least 4 characters long !")
     */
    private $newPassword;

    /**
     * @Assert\EqualTo(propertyPath="newPassword", message="You did not correctly confirm your password !")
     */
    private $confirmPassword;

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }
}