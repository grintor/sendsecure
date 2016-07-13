from secure_smtpd import SMTPServer
from email import Parser
from requests import post
from json import dumps
		
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

			# parse the email into an object
			message_data = Parser.Parser().parsestr(message_data)
			
			# check if the message is multipart (has attachments)
			is_multipart = message_data.is_multipart()

			# in-place convert the message_data object to json
			message_data = dumps(message_data, default=lambda o: o.__dict__, ensure_ascii=False)

			post("https://www.sendsecure.org/smtp_post.php", data = {	'peer':peer,
																		'mailfrom':mailfrom,
																		'rcpttos':rcpttos,
																		'username':authSMTP.username,
																		'password':authSMTP.password,
																		'is_multipart':is_multipart,
																		'message_data':message_data})

authSmtpServer = authSMTP.server(
	('127.0.0.1', 2525),
	None,
	require_authentication=True,
	ssl=False,
	credential_validator=authSMTP.credentialManager(),
	)

authSmtpServer.run()
