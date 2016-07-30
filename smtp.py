from secure_smtpd import SMTPServer
from subprocess import Popen, PIPE, STDOUT
from requests import post
from httplib import HTTPSConnection
import json
		
class authSMTP():

	def __init__(self):
		self.username = None
		self.password = None

	class credentialManager(object):
		def validate(self, username, password):
			r = post("https://www.sendsecure.org/smtp_auth.php", data = {'username':username, 'password':password})
			if r.status_code == 202:
				authSMTP.username = username
				authSMTP.password = password
				return True
			return False

	class server(SMTPServer):
		def process_message(self, peer, mailfrom, rcpttos, message_data):
			p = Popen(['python', 'mailtojson.py', '-p'], stdout=PIPE, stdin=PIPE, stderr=STDOUT)
			mailtojson_stdout = p.communicate(input=message_data)[0]
			#print(mailtojson_stdout.decode())
			mailtojson_stdout = json.loads(mailtojson_stdout.decode())
			mailtojson_stdout['_peer'] = peer;
			mailtojson_stdout['_mailfrom'] = mailfrom;
			mailtojson_stdout['_rcpttos'] = rcpttos;
			mailtojson_stdout['_smtpuser'] = authSMTP.username;
			mailtojson_stdout['_smtppass'] = authSMTP.password;
			
			conn = HTTPSConnection("www.sendsecure.org")
			headers = { "charset" : "utf-8", "Content-Type": "application/json", "User-Agent": "SendSecure/MailEncode 1.0" }
			postJson = json.dumps(mailtojson_stdout, ensure_ascii = False)
			conn.request("POST", "/smtp_post.php", postJson.encode('utf-8'), headers)
			response = conn.getresponse()
			print(response.read())
			conn.close()


authSmtpServer = authSMTP.server(
	('127.0.0.1', 2525),
	None,
	require_authentication=True,
	ssl=False,
	credential_validator=authSMTP.credentialManager(),
	)

authSmtpServer.run()
