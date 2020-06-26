<?php

namespace TeaFacebook\Streamer;

use TeaFacebook\Streamer\Exceptions\StreamerException;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 * @package \TeaFacebook\Streamer
 */
final class Streamer
{
  /**
   * @var string
   */
  private $baseUrl = "https://m.facebook.com";

  /**
   * @var string
   */
  private $userAgent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:77.0) Gecko/20100101 Firefox/77.0";

  /**
   * @var string
   */
  private $cookieFile;

  /**
   * @var int
   */
  private $maxRetry = 3;

  /**
   * @var int
   */
  private $timeout = 30;

  /**
   * @var int
   */
  private $connectTimeout = 30;

  /**
   * @var bool
   */
  private $sslVerifyPeer = true;

  /**
   * @var bool
   */
  private $sslVerifyHost = true;

  /**
   * @param string $cookieFile
   * @throws \TeaFacebook\Streamer\StreamerException
   *
   * Constructor.
   */
  public function __construct(string $cookieFile)
  {
    if (!file_exists($cookieFile)) {
      touch($cookieFile);
      if (!file_exists($cookieFile)) {
        throw new StreamerException("Cannot create cookie file: \"{$cookieFile}\"");
      }
    }

    if (!is_writeable($cookieFile)) {
      throw new StreamerException("Cookie file is not writeable \"{$cookieFile}\"");
    }

    if (!is_readable($cookieFile)) {
      throw new StreamerException("Cookie file is not writeable \"{$cookieFile}\"");
    }

    $this->cookieFile = realpath($cookieFile);
  }

  /**
   * @param string $userAgent
   * @return void
   */
  public function setUserAgent(string $userAgent): void
  {
    $this->userAgent = $userAgent;
  }

  /**
   * @param int $timeout
   * @return void
   */
  public function setTimeout(int $timeout): void
  {
    $this->timeout = $timeout;
  }

  /**
   * @param int $connectTimeout
   * @return void
   */
  public function setConnectTimeout(int $connectTimeout): void
  {
    $this->connectTimeout = $connectTimeout;
  }

  /**
   * @param bool $verify
   * @return void
   */
  public function setSslVerifyHost(bool $verify)
  {
    $this->sslVerifyHost = $verify;
  }

  /**
   * @param bool $verify
   * @return void
   */
  public function setSslVerifyPeer(bool $verify)
  {
    $this->sslVerifyHost = $verify;
  }

  /**
   * @param string $uri
   * @param array  $opt
   * @return ?array
   */
  public function curl(string $uri, array $opt = []): ?array
  {
    $retryCounter = 0;

    if (!filter_var($uri, FILTER_VALIDATE_URL)) {
      $uri = $this->baseUrl."/".ltrim($uri, "/");
    }

    $headers = [];
    $optf = [
      CURLOPT_ENCODING => "gzip",
      CURLOPT_USERAGENT => $this->userAgent,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => $this->sslVerifyPeer,
      CURLOPT_SSL_VERIFYHOST => $this->sslVerifiHost,
      CURLOPT_HEADERFUNCTION => function ($ch, $str) use (&$headers) {
        $len = strlen($str);
        if ($len < 2) return $len; // skip invalid header.

        $str = explode(":", $str, 2);
        if (count($str) > 1) {
          $headers[strtolower(trim($str[0]))] = trim($str[1]);
        }

        return $len;
      },
      CURLOPT_COOKIEJAR => $this->cookieFile,
      CURLOPT_COOKIEFILE => $this->cookieFile,
    ];

    foreach ($opt as $k => $v) {
      $optf[$k] = $v;
    }

    $ch = curl_init($uri);
    curl_setopt_array($ch, $optf);
    $o = [
      "out" => curl_exec($ch),
      "hdr" => $headers,
      "err" => curl_error($ch),
      "ern" => curl_errno($ch),
      "info" => curl_getinfo($ch)
    ];
    curl_close($ch);

    if ($o["err"]) {
      if ($retryCounter < $this->maxRetry) {
        
      }
    }

    return $o;
  }
}
