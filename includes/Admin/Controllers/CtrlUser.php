<?php

namespace Gaterdata\Admin\Controllers;

use Gaterdata\Core\Debug;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Http\Request;
use Slim\Http\Response;
use PHPMailer\PHPMailer\PHPMailer;
use phpmailerException;
use Gaterdata\Core\ApiException;
use Gaterdata\Core\Hash;
use GuzzleHttp\Client;

/**
 * Class CtrlUser.
 *
 * @package Gaterdata\Admin\Controllers
 */
class CtrlUser extends CtrlBase {

  /**
   * Roles allowed to visit the page.
   * 
   * @var array
   */
  const PERMITTED_ROLES = [
    'Administrator',
    'Account manager',
    'Application manager',
  ];

  /**
   * Display the user page.
   *
   * @param Request $request
   *   Request object.
   * @param Response $response
   *   Response object.
   * @param array $args
   *   Request args.
   *
   * @return ResponseInterface
   *   Response.
   *
   * @throws GuzzleException
   */
  public function index(Request $request, Response $response, array $args) {
    // Validate access.
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $this->getAccessRights($response, $username);
    if (!$this->checkAccess()) {
      $this->flash->addMessage('error', 'Access admin: access denied');
      return $response->withStatus(302)->withHeader('Location', '/');
    }

    $menu = $this->getMenus();
    $uid = $args['uid'];
    $mode = strpos($request->getUri()->getPath(), 'edit') !== FALSE ? 'edit' : 'view';

    try {
      $domain = $this->settings['api']['url'];
      $account = $this->settings['api']['core_account'];
      $application = $this->settings['api']['core_application'];
      $token = $_SESSION['token'];
      $client = new Client(['base_uri' => "$domain/$account/$application/"]);
      $result = $client->request('GET', 'user', [
        'headers' => [
          'Authorization' => "Bearer $token",
        ],
        'query' => [
          'uid' => $uid,
        ],
      ]);
      $user = (array) json_decode($result->getBody()->getContents());
    } 
    catch (ClientException $e) {
      $result = $e->getResponse();
      $this->flash->addMessage('error', $this->getErrorMessage($e));
      switch ($result->getStatusCode()) {
        case 401: 
          return $response->withStatus(302)->withHeader('Location', '/login');
          break;
        default:
          $user = [];
          break;
      }
    }

    return $this->view->render($response, 'user.twig', [
      'menu' => $menu,
      'user' => $user,
      'mode' => $mode,
    ]);
  }

  /**
   * Update a user.
   *
   * @param Request $request
   *   Request object.
   * @param Response $response
   *   Response object.
   * @param array $args
   *   Request args.
   *
   * @return ResponseInterface
   *   Response.
   *
   * @throws GuzzleException
   */
  public function update(Request $request, Response $response, array $args) {
    // Validate access.
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $this->getAccessRights($response, $username);
    if (!$this->checkAccess()) {
      $this->flash->addMessage('error', 'Access admin: access denied');
      return $response->withStatus(302)->withHeader('Location', '/');
    }

    $menu = $this->getMenus();
    $allPostVars = $request->getParams();
    $uid = $args['uid'];

    try {
      $domain = $this->settings['api']['url'];
      $account = $this->settings['api']['core_account'];
      $application = $this->settings['api']['core_application'];
      $token = $_SESSION['token'];
      $client = new Client(['base_uri' => "$domain/$account/$application/"]);
      $result = $client->request('PUT', "user/$uid", [
        'headers' => [
          'Authorization' => "Bearer $token",
        ],
        'query' => $allPostVars,
      ]);
    }
    catch (ClientException $e) {
      $result = $e->getResponse();
      $this->flash->addMessage('error', $this->getErrorMessage($e));
      switch ($result->getStatusCode()) {
        case 401:
          return $response->withStatus(302)->withHeader('Location', '/login');
          break;
        default:
          break;
      }
    }

    try {
      $result = $client->request('GET', 'user', [
        'headers' => [
          'Authorization' => "Bearer $token",
        ],
        'query' => [
          'uid' => $uid,
        ],
      ]);
      $user = (array) json_decode($result->getBody()->getContents());
    }
    catch (ClientException $e) {
      $result = $e->getResponse();
      $this->flash->addMessage('error', $this->getErrorMessage($e));
      switch ($result->getStatusCode()) {
        case 401:
          return $response->withStatus(302)->withHeader('Location', '/login');
          break;
        default:
          $user = [];
          break;
      }
    }

    return $this->view->render($response, 'user.twig', [
      'menu' => $menu,
      'user' => $user,
      'mode' => 'edit',
    ]);
  }

  /**
   * Delete a user account and its associated roles.
   *
   * @param \Slim\Http\Request $request
   *   Request object.
   * @param \Slim\Http\Response $response
   *   Response object.
   * @param array $args
   *   Request args.
   *
   * @return ResponseInterface
   *   Response.
   *
   * @throws GuzzleException
   */
  public function delete(Request $request, Response $response, array $args) {
    // Validate access.
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $this->getAccessRights($response, $username);
    if (!$this->checkAccess()) {
      $this->flash->addMessage('error', 'Access admin: access denied');
      return $response->withStatus(302)->withHeader('Location', '/');
    }

    $menu = $this->getMenus();
    $uid = $args['uid'];

    try {
      $domain = $this->settings['api']['url'];
      $account = $this->settings['api']['core_account'];
      $application = $this->settings['api']['core_application'];
      $token = $_SESSION['token'];
      $client = new Client(['base_uri' => "$domain/$account/$application/"]);
      $result = $client->request('DELETE', "user/$uid", [
        'headers' => [
          'Authorization' => "Bearer $token",
        ],
      ]);
      $this->flash->addMessage('info', 'User successfully deleted.');
    }
    catch (ClientException $e) {
      $result = $e->getResponse();
      $this->flash->addMessage('error', $this->getErrorMessage($e));
      switch ($result->getStatusCode()) {
        case 401:
          return $response->withStatus(302)->withHeader('Location', '/login');
          break;
        default:
          break;
      }
    }

    return $response->withStatus(302)->withHeader('Location', '/users');
  }

  /**
   * Send a user an email with a token to register.
   *
   * The email templates are in /includes/Admin/templates/invite-user.email.twig.
   *
   * @param Request $request
   *   Slim request object.
   * @param Response $response
   *   Slim response object.
   * @param array $args
   *   Slim args array
   * .
   * @return ResponseInterface|Response
   *
   * @throws \PHPMailer\PHPMailer\Exception
   */
  public function invite(Request $request, Response $response, array $args) {
    // Validate access.
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $this->getAccessRights($response, $username);
    if (!$this->checkAccess()) {
      $this->flash->addMessage('error', 'Access admin: access denied');
      return $response->withStatus(302)->withHeader('Location', '/');
    }

    $allPostVars = $request->getParsedBody();
    if (empty($email = $allPostVars['invite-email'])) {
      $this->flash->addMessage('error', 'Invite user: email not specified');
      return $response->withRedirect('/users');
    }

    // Check if user already exists.
    try {
      $userHlp = new User($this->dbSettings);
      $user = $userHlp->findByEmail($email);
      if (!empty($user['uid'])) {
        return $this->view->render($response, 'users.twig', [
          'menu' => $menu,
          'message' => [
            'type' => 'error',
            'text' => 'A user already exists with this email: ' . $email,
          ]
        ]);
      }
    } catch (ApiException $e) {
      return $this->view->render($response, 'users.twig', [
        'menu' => $menu,
        'message' => [
          $message['type'] = 'error',
          $message['text'] = $e->getMessage(),
        ]
      ]);
    }

    // Generate vars for the email.
    $token = Hash::generateToken($email);
    $host = $this->getHost();
    $scheme = $request->getUri()->getScheme();
    $link = "$scheme://$host/user/register/$token";

    // Add invite to DB.
    try {
      $accountHlp = new Account($this->dbSettings);
      $account = $accountHlp->findByUaid($uaid);
      $inviteHlp = new Invite($this->dbSettings);
      // Remove any old invites for this email.
      $inviteHlp->deleteByEmail($email);
      // Add new invite.
      $inviteHlp->create($account['accid'], $email, $token);
    } catch (ApiException $e) {
      return $this->view->render($response, 'users.twig', [
        'menu' => $menu,
        'message' => [
          'type' => 'error',
          'text' => $e->getMessage(),
        ]
      ]);
    }

    // Send the email.
    $mail = new PHPMailer(TRUE); // Passing `true` enables exceptions
    try {
      //Server settings
      $mail->SMTPDebug = $this->mailSettings['debug'];
      $mail->isSMTP($this->mailSettings['smtp']);
      $mail->Host = $this->mailSettings['host'];
      $mail->SMTPAuth = $this->mailSettings['auth'];
      $mail->Username = $this->mailSettings['username'];
      $mail->Password = $this->mailSettings['password'];
      $mail->SMTPSecure = $this->mailSettings['smtpSecure'];
      $mail->Port = $this->mailSettings['port'];

      //Recipients
      $mail->addAddress($email);
      $mail->setFrom($this->mailSettings['from']['email'], $this->mailSettings['from']['name']);
      $mail->addReplyTo($this->mailSettings['from']['email'], $this->mailSettings['from']['name']);

      //Content
      $mail->Subject = $this->view->fetchBlock('invite-user.email.twig', 'subject');
      $mail->Body = $this->view->fetchBlock('invite-user.email.twig', 'body_html', [
        'link' => $link,
      ]);
      $mail->AltBody = $this->view->fetchBlock('invite-user.email.twig', 'body_text', [
        'link' => $link,
      ]);

      $mail->send();

      $message['text'] = 'Invite has been sent to ' . $email;
      $message['type'] = 'info';
      return $this->view->render($response, 'users.twig', [
        'menu' => $menu,
        'message' => $message,
      ]);
    } catch (phpmailerException $e) {
      return $this->view->render($response, 'users.twig', [
        'menu' => $menu,
        'message' => [
          'type' => 'error',
          'text' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo,
        ]
      ]);
    }
  }

  /**
   * Allow a user with a valid token to register.
   *
   * @param \Slim\Http\Request $request
   *   Request object.
   * @param \Slim\Http\Response $response
   *   Response object.
   * @param array $args
   *   Request args.
   *
   * @return ResponseInterface
   *   Response.
   */
  public function register(Request $request, Response $response, array $args) {
    $menu = $this->getMenus([]);
    if ($request->isPost()) {
      $allVars = $request->getParsedBody();
    } else {
      $allVars = $args;
    }

    // Token not received.
    if (empty($allVars['token'])) {
      return $response->withRedirect('/login');
    }

    $token = $allVars['token'];

    // Invalid token.
    $inviteHlp = new Invite($this->dbSettings);
    $invite = $inviteHlp->findByToken($token);
    if (empty($invite['iid'])) {
      return $response->withRedirect('/login');
    }

    // Validate User is not already in the system.
    $userHlp = new User($this->dbSettings);
    $user = $userHlp->findByEmail($invite['email']);
    if (!empty($user['uid'])) {
      $inviteHlp->deleteByEmail($invite['email']);
      $message['text'] = 'Your user already exists: ' . $invite['email'];
      $message['type'] = 'error';
      return $this->view->render($response, 'home.twig', [
        'menu' => $menu,
        'message' => $message,
      ]);
    }

    if ($request->isGet()) {
      // Display the register form.
      return $this->view->render($response, 'register.twig', [
        'menu' => $menu,
        'token' => $token,
      ]);
    }

    // Fall through to new user register post form submission.
    return $this->createUser($allVars, $menu, $response);
  }

  /**
   * Create a new user form form submission.
   *
   * @param array $allVars
   *   User vars and token.
   * @param array $menu
   *   Menu items.
   * @param \Slim\Http\Response $response
   *   Response object.
   *
   * @return ResponseInterface
   *   Response.
   */
  private function createUser($allVars, $menu, $response) {
    if (empty($allVars['token']) ||
      empty($allVars['username']) ||
      empty($allVars['password']) ||
      empty($allVars['honorific']) ||
      empty($allVars['email']) ||
      empty($allVars['name_first']) ||
      empty($allVars['name_last'])) {
      // Missing mandatory fields.
      $message['text'] = "Required fields not entered.";
      $message['type'] = 'error';
      return $this->view->render($response, 'register.twig', [
        'menu' => $menu,
        'message' => $message,
        'token' => $allVars['token'],
      ]);
    }

    // Ensure email matches the invite token.
    $inviteHlp = new Invite($this->dbSettings);
    $invite = $inviteHlp->findByToken($allVars['token']);
    if ($invite['email'] != $allVars['email']) {
      // Delete the invite.
      $inviteHlp->deleteByToken($allVars['token']);
      // Redirect to login.
      $message['type'] = 'error';
      $message['text'] = "Your email does not match the invite email. Please resend the invite.";
      return $this->view->render($response, 'login.twig', [
        'menu' => $menu,
        'message' => $message,
      ]);
    }

    // Validate username and email does not exist.
    $userHlp = new User($this->dbSettings);
    $result = $userHlp->findByEmail($allVars['email']);
    if (!empty($result['uid'])) {
      $message['type'] = 'error';
      $message['text'] = 'A user already exists with this email: ' . $allVars['email'] . '.';
      $message['text'] .= 'Please login.';
      return $this->view->render($response, 'login.twig', [
        'menu' => $menu,
        'message' => $message,
      ]);
    }
    $result = $userHlp->findByUsername($allVars['username']);
    if (!empty($result['uid'])) {
      $message['type'] = 'error';
      $message['text'] = 'A user already exists with this username: ' . $allVars['username'] . '.';
      $message['text'] .= 'Please use a different username.';
      return $this->view->render($response, 'register.twig', [
        'menu' => $menu,
        'message' => $message,
        'token' => $allVars['token'],
      ]);
    }

    $newUser = $userHlp->create(
      !empty($allVars['username']) ? $allVars['username'] : '',
      !empty($allVars['password']) ? $allVars['password'] : '',
      !empty($allVars['email']) ? $allVars['email'] : '',
      !empty($allVars['honorific']) ? $allVars['honorific'] : '',
      !empty($allVars['name_first']) ? $allVars['name_first'] : '',
      !empty($allVars['name_last']) ? $allVars['name_last'] : '',
      !empty($allVars['company']) ? $allVars['company'] : '',
      !empty($allVars['website']) ? $allVars['website'] : '',
      !empty($allVars['address_street']) ? $allVars['address_street'] : '',
      !empty($allVars['address_suburb']) ? $allVars['address_suburb'] : '',
      !empty($allVars['address_city']) ? $allVars['address_city'] : '',
      !empty($allVars['address_state']) ? $allVars['address_state'] : '',
      !empty($allVars['address_country']) ? $allVars['address_country'] : '',
      !empty($allVars['address_postcode']) ? $allVars['address_postcode'] : '',
      !empty($allVars['phone_mobile']) ? $allVars['phone_mobile'] : '',
      !empty($allVars['phone_work']) ? $allVars['phone_work'] : ''
    );
    if (!$newUser['uid']) {
      $message['text'] = "Sorry, there was an error creating your user. Please speak to your administrator.";
      $message['type'] = 'error';
      return $this->view->render($response, 'login.twig', [
        'menu' => $menu,
        'message' => $message,
      ]);
    }

    // Assign the user to the account.
    try {
      $userAccountHlp = new UserAccount($this->dbSettings);
      $userAccountHlp->create($invite['accid'], $newUser['uid']);
    } catch (ApiException $e) {
      $message['text'] = "Sorry, there was an error assigning your user to the account. Please speak to your administrator.";
      $message['type'] = 'error';
      return $this->view->render($response, 'login.twig', [
        'menu' => $menu,
        'message' => $message,
      ]);
    }

    // Delete the invite.
    $inviteHlp->deleteByToken($allVars['token']);

    // Redirect to login.
    $message['text'] = "Congratulations, you are registered. Please login.";
    $message['type'] = 'info';
    return $this->view->render($response, 'login.twig', [
      'menu' => $menu,
      'message' => $message,
    ]);
  }

  /**
   * Get Local domain name.
   *
   * @return string
   *   Host name.
   */
  private function getHost() {
    $possibleHostSources = ['HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'];
    $sourceTransformations = [
      "HTTP_X_FORWARDED_HOST" => function($value) {
        $elements = explode(',', $value);
        return trim(end($elements));
      }
    ];
    $host = '';
    foreach ($possibleHostSources as $source) {
      if (!empty($host)) {
        break;
      }
      if (empty($_SERVER[$source])) {
        continue;
      }
      $host = $_SERVER[$source];
      if (array_key_exists($source, $sourceTransformations)) {
        $host = $sourceTransformations[$source]($host);
      }
    }

    // Remove port number from host
    $host = preg_replace('/:\d+$/', '', $host);

    return trim($host);
  }

}
