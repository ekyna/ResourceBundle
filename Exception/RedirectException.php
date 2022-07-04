<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Exception;

use Exception;

/**
 * Class RedirectException
 * @package Ekyna\Bundle\ResourceBundle\Exception
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RedirectException extends Exception
{
    private string $path;
    private string $messageType;


    /**
     * Constructor.
     *
     * @param string      $path        The path to redirect to (an absolute path (/foo), an absolute URL (http://...), or a route name (foo)).
     * @param string|null $message     The (flash) message.
     * @param string      $messageType The (flash) message type.
     */
    public function __construct(string $path, string $message = '', string $messageType = 'info')
    {
        parent::__construct($message);

        $this->path = $path;
        $this->messageType = $messageType;
    }

    /**
     * Sets the path.
     *
     * @param string $path
     *
     * @return RedirectException
     */
    public function setPath(string $path): RedirectException
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns the path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Sets the (flash) message type.
     *
     * @param string $type
     *
     * @return RedirectException
     */
    public function setMessageType(string $type): RedirectException
    {
        $this->messageType = $type;

        return $this;
    }

    /**
     * Returns the (flash) message type.
     *
     * @return string
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }
}
