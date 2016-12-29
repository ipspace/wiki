<?php
/**
 * MediaWiki cookie-based session provider interface
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Session
 */

namespace MediaWiki\Session;

use Config;
use User;
use WebRequest;
use RequestContext;

/**
 * A CookieSessionProvider persists sessions using cookies
 *
 * @ingroup Session
 * @since 1.27
 */


class CentralSessionProvider extends CookieSessionProvider {

  const SALT = 'MyM4g1cVa$ue';

	public function provideSessionInfo( WebRequest $request ) {
		global $cspAuthentication;
		global $wgGroupPermissions;

    $uname = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_URL);
    if (!$uname) {
      return parent::provideSessionInfo($request);
    }
		if ($cspAuthentication) return null;

    $this->logger->warning('CSP: USER specified, proceeding with login');

		$uname = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_URL);
		if (!$uname) return null;

		$email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);
    $check = filter_input(INPUT_GET, 'check', FILTER_SANITIZE_EMAIL);
    if (!$check) {
      throw new \InvalidArgumentException( __METHOD__ . ': Digital signature is missing' );
    }
    if (!$email) {
      throw new \InvalidArgumentException( __METHOD__ . ': Email is missing' );
    }

    $md5 = md5($uname.$email.self::SALT);
    if ($md5 != $check) {
      throw new \InvalidArgumentException( __METHOD__ . ": Digital signature doesn't match");
    }
		$cspAuthentication = true;
		$wgGroupPermissions['*']['createaccount'] = true;

		$user = User::newFromName($uname);
		$user->setEmail($email);
		$uinfo = UserInfo::newFromUser($user,true);
		$sinfo = new SessionInfo ($this->priority,[
			'provider' => $this,
			'userInfo' => $uinfo
			]);

		$session = SessionManager::getGlobalSession();
#    $session = SessionManager::getSessionForRequest($request);
    $session->setUser($user);
#		$session->persist();

    $context = RequestContext::getMain();
    $wgUser = $user;
    $context->setUser( $user );

		$cspAuthentication = false;
		return $sinfo;
	}
}