<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $lastname;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;


    /**
     * @Doctrine\ORM\Mapping\Column(type="string", length=300)
     */
    private $image;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function createAvatar()
    {
        $fn = $this->firstname;
        $ln = $this->lastname;
        $em = $this->email;
        $siteRoot = __DIR__ . '/../../public/images/';

        $newUserSubfolder = $siteRoot . $em;
        if (!file_exists($newUserSubfolder)) {
            mkdir($newUserSubfolder, 0777, true);
        }

        $fnInt = 0;
        $lnInt = 0;
        $emInt = 0;

        $x = strlen($fn);
        $y = strlen($ln);

        for ($i = 0; $i < $x - 1; $i++) {
            $fnInt += ord($fn[$i]);
        }

        for ($i = 0; $i < $y - 1; $i++) {
            $lnInt += ord($ln[$i]);
        }

        for ($i = 0; $em[$i] != '@'; $i++) {
            $emInt += ord($em[$i]);
        }

        $fnColor = $fnInt;
        $lnColor = $lnInt;
        $emColor = $emInt;

        while ($fnColor > 235) {
            $fnColor = $fnColor / 2 + 40;
        }

        while ($lnColor > 235) {
            $lnColor = $lnColor / 2 + 40;
        }

        while ($emColor > 235) {
            $emColor = $emColor / 2 + 40;
        }

        $total = ($fnInt + $lnInt + $emInt) * 21;
        $im = imagecreate(420, 420);
        $white = ImageColorAllocate($im, 255, 255, 255);
        $color = ImageColorAllocate($im, $fnColor, $lnColor, $emColor);
        ImageFilledRectangle($im, 0, 0, 420, 420, $white);

        $this->draw($im, $total, $color);

        $save = $newUserSubfolder . '/avatar.jpeg';
        imagejpeg($im, $save, 100);   //Saves the image

        imagedestroy($im);
    }

    public function draw($im, $total, $color)
    {
        $startX = 35;
        $startY = 35;
        for ($i = 0; $i < 5; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if (pow(2, $i * 3 + $j) & $total) {
                    ImageFilledRectangle($im, $startX, $startY, $startX + 70, $startY + 70, $color);
                    if ($j != 2) {
                        ImageFilledRectangle($im, 315 - 70 * $j, $startY, 385 - 70 * $j, $startY + 70, $color);
                    }
                }

                $startX += 70;
            }

            $startX = 35;
            $startY += 70;
        }

        return $im;
    }
}
