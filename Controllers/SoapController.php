<?php
namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class SOAPController
{
	/**
	 * Store data to database
	 *
	 * @return void
	 */
	public function post()
	{
		$validator = Validation::createValidator();

		$groups = new Assert\GroupSequence(['Default', 'custom']);

		$constraint = new Assert\Collection([
			'ipAddress' => new Assert\Ip(NULL, 'all'),
		]);

		$response = new Response();
		$response->headers->set('Content-Type', 'text/xml');
		$encoder = new XmlEncoder();

		try {
			$stream_context = stream_context_create([
				'http' => [
					'protocol_version' => 1.1
				],
			]);
	
			$client = new \SoapClient(SERVICE_URL, [
				'trace'          => 1,
				'soap_version'   => SOAP_1_2,
				'stream_context' => $stream_context,
			]);

			$request = Request::createFromGlobals();

			$violations = $validator->validate($request->request->all(), $constraint, $groups);

			if (0 !== count($violations)) {
				$result['status'] = 'error';
				foreach ($violations as $violation) {
					$result['messages'][] = [
						'property' => $violation->getPropertyPath(),
						'value'    => $violation->getInvalidValue(),
						'message'  => $violation->getMessage()
					];
				}

				$response->setContent($encoder->encode($result, 'xml'));
			} else {
				$params = [
					'ipAddress'  => $request->request->get('ipAddress'),
					'licenseKey' => ''
				];
				$client->ResolveIP($params);				

				$response->setContent($client->__getLastResponse());
			}
		} catch (\SoapFault $e) {
			echo $e->getMessage();
		}

		return $response->send();
	}

	/**
	 * For testing
	 *
	 * @return string
	 */
	public function get()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://ident.me');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$userIp = curl_exec($ch);
		curl_close($ch);

		// validate ip (just in case)
		$userIp = array_pop(preg_grep('/\b((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}\b/',
								explode("\n", $userIp)));
		?>
		<form target="_blank" action="/" method="POST" style="margin: 30px">
			<table cellspacing="0" cellpadding="4" frame="box" bordercolor="#dcdcdc" rules="none" style="border-collapse: collapse;">
				<tbody>
					<tr>
						<td class="frmHeader" style="border-right: 2px solid white;background: #dcdcdc;">Parameter</td>
						<td class="frmHeader" style="background: #dcdcdc;">Value</td>
					</tr>
					<tr>
						<td class="frmText" style="color: #000000; font-weight: normal;">ipAddress:</td>
						<td><input class="frmInput" type="text" size="50" name="ipAddress" value="<?php echo $userIp ?>"></td>
					</tr>
					<tr>
						<td></td>
						<td align="right"> <input type="submit" value="Invoke" class="button" style="background: #dcdcdc;"></td>
					</tr>
				</tbody>
			</table>
		</form>
		<?php
	}
}