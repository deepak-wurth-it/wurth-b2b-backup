<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Wcb\ApiConnect\Api\Data;

interface SoapClientInterface
{

    const CONTENT = 'content';
    const SOAPCLIENT_ID = 'soapclient_id';

    /**
     * Get soapclient_id
     * @return string|null
     */
    public function getSoapclientId();

    /**
     * Set soapclient_id
     * @param string $soapclientId
     * @return \Wcb\ApiConnect\SoapClient\Api\Data\SoapClientInterface
     */
    public function setSoapclientId($soapclientId);

    /**
     * Get content
     * @return string|null
     */
    public function getContent();

    /**
     * Set content
     * @param string $content
     * @return \Wcb\ApiConnect\SoapClient\Api\Data\SoapClientInterface
     */
    public function setContent($content);
}

