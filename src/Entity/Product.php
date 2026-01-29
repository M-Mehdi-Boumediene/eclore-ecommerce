<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mainPhoto = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mainVideo = null;

    // ✅ Relation ManyToOne vers ProductCategory
    #[ORM\ManyToOne(targetEntity: ProductCategory::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductCategory $productCategory = null;

    // ✅ Relation ManyToMany vers Color
    /**
     * @var Collection<int, Color>
     */
    #[ORM\ManyToMany(targetEntity: Color::class, inversedBy: 'products')]
    private Collection $colors;

    // ✅ Relation OneToMany vers ProductImage
    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductImage::class, cascade: ['persist', 'remove'])]
    private Collection $productImages;

    public function __construct()
    {
        $this->colors = new ArrayCollection();
        $this->productImages = new ArrayCollection();
    }

    // ===========================
    // Getters & Setters
    // ===========================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? ''; // 🔹 permet à EasyAdmin d’afficher correctement
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getMainPhoto(): ?string
    {
        return $this->mainPhoto;
    }

    public function setMainPhoto(?string $mainPhoto): static
    {
        $this->mainPhoto = $mainPhoto;
        return $this;
    }

    public function getMainVideo(): ?string
    {
        return $this->mainVideo;
    }

    public function setMainVideo(?string $mainVideo): static
    {
        $this->mainVideo = $mainVideo;
        return $this;
    }

    public function getProductCategory(): ?ProductCategory
    {
        return $this->productCategory;
    }

    public function setProductCategory(?ProductCategory $productCategory): static
    {
        $this->productCategory = $productCategory;
        return $this;
    }

    /**
     * @return Collection<int, Color>
     */
    public function getColors(): Collection
    {
        return $this->colors;
    }

    public function addColor(Color $color): static
    {
        if (!$this->colors->contains($color)) {
            $this->colors->add($color);
        }
        return $this;
    }

    public function removeColor(Color $color): static
    {
        $this->colors->removeElement($color);
        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImage $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduct($this);
        }
        return $this;
    }

    public function removeProductImage(ProductImage $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }
        return $this;
    }
}
