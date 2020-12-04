<?php
class Divido_WSSoapClient extends SoapClient {


	/**
	 * Debug-Ausgabe steuern
	 *
	 * @var bool
	 */
	private $bDebugWSSoapClient = false;

	private $sOasisBase = 'http://docs.oasis-open.org/wss/2004/01';
	private $sOasisExtension = '/oasis-200401-wss-wssecurity-secext-1.0.xsd';

	/**
	 * WS-Security Timestamp in XML Standard Format
	 * @var string
	 */
	private $sTimestamp = '';

	/**
	 * WS-Security Number for added security
	 * @var int
	 */
	private $iNonce = 0;

	/**
	 * WS-Security Username
	 * @var string
	 */
	private $sUsername = "sinkacomTest";

	/**
	 * WS-Security Password
	 * @var string
	 */
	private $sPassword = "SinkaCom2015";

	/**
	 * WS-Security PasswordType
	 * @var string
	 */
	private $sPasswordType = "PasswordDigest";

	/**
	 * Get SoapClient Request-Info
	 *
	 */
	public function getLastRequestInfo() {
		//$this->__get;
	}

	/**
	 * Set WS-Security credentials
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $passwordType
	 */
	public function __setUsernameToken( $username, $password, $passwordType ) {
		$this->sUsername = $username;
		$this->sPassword = $password;
		$this->sPasswordType = $passwordType;
	}

	/**
	 * Overwrites the original method adding the security header.
	 * As you can see, if you want to add more headers, the method needs to be modified.
	 */
	public function __call( $sFunctionName, $aArguments ) {
		$this->__setSoapHeaders($this->generateWSSecurityHeader());
		return parent::__call($sFunctionName, $aArguments);
	}

	/**
	 * Generate password digest.
	 *
	 * @return string   base64 encoded password digest
	 */
	private function generatePasswordDigest() {
		$this->iNonce = mt_rand();
		$this->sTimestamp = gmdate('Y-m-d\TH:i:s\Z');

		$sPackedNonce = pack('H*', $this->iNonce);
		$sPackedTimestamp = pack('a*', $this->sTimestamp);
		$sPackedPassword = pack('a*', $this->sPassword);

		$sHash = sha1($sPackedNonce.$sPackedTimestamp.$sPackedPassword);
		$sPackedHash = pack('H*', $sHash);

		return base64_encode($sPackedHash);
	}

	/**
	 * Generates WS-Security headers
	 *
	 * @return SoapHeader
	 */
	private function generateWSSecurityHeader() {
		if ( $this->sPasswordType === 'PasswordDigest' ) {
			$sPassword = $this->generatePasswordDigest();
			$sNonceBefore = sha1($this->iNonce);

			$sNonce = base64_encode(pack('H*', $this->iNonce));

			if ( $this->bDebugWSSoapClient ) {
				echo "<div style='display: none;' class='debug'>";
				echo "<h4>$sNonceBefore</h4>";
				echo "<h4>$sNonce</h4>";
				echo "</div>";

			}
		} elseif ( $this->sPasswordType === 'PasswordText' ) {
			$sPassword = $this->sPassword;
			$sNonce = sha1(mt_rand());
		} else {
			return '';
		}

		$sXml = '
<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="'.$this->sOasisBase.$this->sOasisExtension.'">
	<wsse:UsernameToken wsu:Id="UsernameToken-1" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
		<wsse:Username>'.$this->sUsername.'</wsse:Username>
		<wsse:Password Type="'.$this->sOasisBase.'/oasis-200401-wss-username-token-profile-1.0#'.$this->sPasswordType.'">'.$sPassword.'</wsse:Password>
		<wsse:Nonce EncodingType="'.$this->sOasisBase.'/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'.$sNonce.'</wsse:Nonce>';

		if ( $this->sPasswordType === 'PasswordDigest' ) {
			$sXml .= "\n\t\t".'<wsu:Created>'.$this->sTimestamp.'</wsu:Created>';
		}

		$sXml .= '
	</wsse:UsernameToken>
</wsse:Security>';

		if ( $this->bDebugWSSoapClient ) {
			echo "<div style='display: none;' class='debug'>";
			var_dump($sXml);
			echo "</div>";
		}

		return new SoapHeader(
			$this->sOasisBase.$this->sOasisExtension,
			'Security',
			new SoapVar($sXml, XSD_ANYXML),
			true);
	}

	public function __doRequest( $sRequest, $sLocation, $sAction, $iVersion, $iOneWay = NULL ) {
		$sLocation = str_replace('http://', 'https://', $sLocation);
		//$request = str_replace('customerAdress','customerAddress',$request);
		return parent::__doRequest($sRequest, $sLocation, $sAction, $iVersion, $iOneWay);
	}
}