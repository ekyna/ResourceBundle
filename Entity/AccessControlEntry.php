<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Entity;

/**
 * Class AccessControlEntry
 * @package Ekyna\Bundle\ResourceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccessControlEntry
{
    private ?int   $id      = null;
    private string $subject;
    private string $namespace;
    private string $resource;
    private string $permission;
    private bool   $granted = false;


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns the subject.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Sets the subject.
     *
     * @param string $subject
     *
     * @return AccessControlEntry
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Returns the namespace.
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Sets the namespace.
     *
     * @param string $namespace
     *
     * @return AccessControlEntry
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Returns the resource.
     *
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Sets the resource.
     *
     * @param string $resource
     *
     * @return AccessControlEntry
     */
    public function setResource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Returns the permission.
     *
     * @return string
     */
    public function getPermission(): string
    {
        return $this->permission;
    }

    /**
     * Sets the permission.
     *
     * @param string $permission
     *
     * @return AccessControlEntry
     */
    public function setPermission(string $permission): self
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Returns the granted.
     *
     * @return bool
     */
    public function getGranted(): bool
    {
        return $this->granted;
    }

    /**
     * Sets the granted.
     *
     * @param bool $granted
     *
     * @return AccessControlEntry
     */
    public function setGranted(bool $granted): self
    {
        $this->granted = $granted;

        return $this;
    }
}
