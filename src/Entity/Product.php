<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255)]
    private ?string $name = null;

    #[ORM\Column(type:"text", nullable:true)]
    private ?string $description = null;

    #[ORM\Column(type:"float")]
    private ?float $price = null;

    #[ORM\Column(type:"datetime")]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type:"string", length:255)]
    private ?string $firebaseUid = null;

    #[ORM\OneToMany(mappedBy: "product", targetEntity: ProductImage::class, cascade: ["persist", "remove"])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        // ⚡ Date automatique à la création
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getPrice(): ?float { return $this->price; }
    public function setPrice(float $price): self { $this->price = $price; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getFirebaseUid(): ?string { return $this->firebaseUid; }
    public function setFirebaseUid(string $firebaseUid): self { $this->firebaseUid = $firebaseUid; return $this; }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getImages(): Collection { return $this->images; }

    public function addImage(ProductImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduct($this);
        }
        return $this;
    }

    public function removeImage(ProductImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }
        return $this;
    }
}
