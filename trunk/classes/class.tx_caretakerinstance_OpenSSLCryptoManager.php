<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Christopher Hlubek (hlubek@networkteam.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


require_once('class.tx_caretakerinstance_AbstractCryptoManager.php');

/**
 * An OpenSSL based Crypto Manager implementation
 * 
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_OpenSSLCryptoManager implements tx_caretakerinstance_AbstractCryptoManager {
	
	/**
	 * Encrypt data with the <em>public</em> key of the recipient.
	 * Will be encrypted using openssl_seal.
	 * 
	 * @param $data string The data to encrypt
	 * @param $key string the public key for encryption (as PEM formatted string)
	 * @return string The encrypted data
	 */
	public function encrypt($data, $publicKey) {
		openssl_seal($data, $cryptedData, $envelopeKeys, array($publicKey));
		
		$envelopeKey = $envelopeKeys[0];

		$crypted = base64_encode($envelopeKey) . ':' . base64_encode($cryptedData);

		return $crypted;
	}

	/**
	 * Decrypt data with <em>private</em> key
	 * 
	 * @param $data string The data to decrypt
	 * @param $key string the private key for decryption (as PEM formatted string)
	 * @return string The decrypted data
	 */
	public function decrypt($data, $privateKey) {
		list($envelopeKey, $cryptedData) = explode(':', $data);
		
		$envelopeKey = base64_decode($envelopeKey);
		$cryptedData = base64_decode($cryptedData);
		
		openssl_open($cryptedData, $decrypted, $envelopeKey, $privateKey);

		return $decrypted;
	}

	public function createSignature($data, $privateKey) {
		openssl_sign($data, $signature, $privateKey);
		$signature = base64_encode($signature);
		return $signature;
	}
	
	public function verifySignature($data, $signature, $publicKey) {
		$signature = base64_decode($signature);
		$correct = openssl_verify($data, $signature, $publicKey);
		return $correct === 1; 
	}

	public function generateKeyPair() {		
		$keyPair = openssl_pkey_new();

		openssl_pkey_export($keyPair, $privateKey);

		$publicKey = openssl_pkey_get_details($keyPair);
		$publicKey = $publicKey['key'];

		return array($publicKey, $privateKey); 
	}
}
?>