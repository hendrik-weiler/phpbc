<?php

namespace Controller\twitter;

use renderer\AjaxRequest;
use renderer\AjaxResponse;
use renderer\Request;

require_once 'Controller.php';

class App extends Controller
{
	public $form_app_text;

	public function like($renderer,AjaxRequest $request,AjaxResponse $response) {
		if(!$this->canAccess()) {
			$response->redirect('index');
		}

		$id = $request->getValue('data-id');
		$result = $this->queryDB('SELECT id FROM post_likes WHERE post_id = "' . $this->escapeString($id) . '" AND account_id = "' . $this->user['id'] . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);
		if(!$row) {
			$res = $this->execDB('INSERT INTO post_likes VALUES(null,' . $this->escapeString($id)  . ',' . $this->user['id'] . ');');
		} else {
			$res = $this->execDB('DELETE FROM post_likes WHERE id = "' . $row['id'] . '"');
		}

		$result = $this->queryDB('SELECT count(*) as count FROM post_likes WHERE post_id = "' . $this->escapeString($id) . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);
		return array(
			'post_id' => $id,
			'count' => $row['count']
		);
	}

	public function retweet($renderer,AjaxRequest $request,AjaxResponse $response) {
		if(!$this->canAccess()) {
			$response->redirect('index');
		}

		$id = $request->getValue('data-id');
		$result = $this->queryDB('SELECT id FROM post_retweets WHERE post_id = "' . $this->escapeString($id) . '" AND account_id = "' . $this->user['id'] . '"');
		$row = $result->fetchArray(SQLITE3_ASSOC);
		if(!$row) {
			$res = $this->execDB('INSERT INTO post_retweets VALUES(null,' . $this->escapeString($id)  . ',' . $this->user['id'] . ');');
			$res = $this->execDB('INSERT INTO post VALUES(null,' . $this->user['id']  . ',"",' . $this->escapeString($id) . ',' . time() . ');');
			return 1;
		}

		return 0;
	}

	public function logout($renderer, $request, $response) {
		if(!$this->canAccess()) {
			$response->redirect('index');
		}
		$this->initDB();
		$res = $this->execDB('UPDATE account SET session = "' . \Tools::generateSessionId() . '" WHERE id = ' . $this->escapeString($this->user['id']));
		\Tools::removeCookie('sid');
		$response->redirect('index');
	}

	public function get_execute($renderer, $request, $response)
	{
		$this->initDB();
		if(!$this->canAccess()) {
			$response->redirect('index');
		}

		$username = $renderer->document->getElementById('username');
		$username->setContent($this->user['username']);

		$logout = $renderer->document->getElementById('logout');
		$logout->addEventListener('click','logout');

		$result = $this->queryDB('SELECT id,message_text,retweet_id,created,
       		(SELECT username FROM account WHERE id = t1.account_id) as username,
       		(SELECT count(*) as count FROM post_retweets WHERE post_id = t1.id) as retweets,
       		(SELECT count(*) as count FROM post_likes WHERE post_id = t1.id) as likes,
       		(SELECT count(*) as count FROM post_likes WHERE post_id = t1.id AND account_id = ' . $this->user['id'] . ') as isLiked,
       		(SELECT count(*) as count FROM post_retweets WHERE post_id = t1.id AND account_id = ' . $this->user['id'] . ') as isRetweeted
       		FROM post t1 ORDER BY created desc;');
		$posts = $renderer->document->getElementById('posts');
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			if($row['retweet_id'] > 0) {
				$retweetQuery = $this->queryDB('SELECT id,message_text,created,(SELECT username FROM account WHERE id = t1.account_id) as username FROM post t1 WHERE id = "' . $row['retweet_id'] . '"');
				$retweet = $retweetQuery->fetchArray(SQLITE3_ASSOC);
				$node = $renderer->document->createFromHTML('
				<div class="tweet-container">
					<div class="tweet-head">' . $row['username'] . ', <i>created at ' . date('Y/d/m H:i',$row['created']) . '</i></div>
					<div class="tweet-body">
						<div class="tweet-retweet">
							<div class="tweet-head">' . $retweet['username'] . ', <i>created at ' . date('Y/d/m H:i',$retweet['created']) . '</i></div>	
							<div class="tweet-body">' . $retweet['message_text'] . '</div>		
						</div>
					</div>
					<div class="tweet-stats">
						<a class="' . ($row['isLiked']==1 ? 'active' : '') . '" data-id="' . $row['id'] . '" id="like' . $row['id'] . '" href="#"><img src="/@/twitter/heart.svg"/><span>' . $row['likes'] . '</span></a></div>
				</div>
			');
				$posts->appendChild($node);
				$like = $renderer->document->getElementById('like' . $row['id']);
				$like->addEventListener('ajaxClick','like');

			} else {
				$node = $renderer->document->createFromHTML('
				<div class="tweet-container">
					<div class="tweet-head">' . $row['username'] . ', <i>created at ' . date('Y/d/m H:i',$row['created']) . '</i></div>
					<div class="tweet-body">' . nl2br($row['message_text']) . '</div>
					<div class="tweet-stats">
						<a class="' . ($row['isLiked']==1 ? 'active' : '') . '" data-id="' . $row['id'] . '" id="like' . $row['id'] . '" href="#"><img src="/@/twitter/heart.svg"/><span>' . $row['likes'] . '</span></a>
						<a class="' . ($row['isRetweeted']==1 ? 'not-allowed' : '') . '" data-id="' . $row['id'] . '" id="retweet' . $row['id'] . '" href="#"><img src="/@/twitter/retweet.svg"/><span>' . $row['retweets'] . '</span></a></div>
				</div>
			');
				$posts->appendChild($node);
				$like = $renderer->document->getElementById('like' . $row['id']);
				$like->addEventListener('ajaxClick','like');

				$retweet = $renderer->document->getElementById('retweet' . $row['id']);
				$retweet->addEventListener('ajaxClick','retweet');
			}

		}
	}

	public function post_execute($renderer, $request, $response)
	{
		$this->get_execute($renderer,$request,$response);

		if(!$request->checkCRSFToken()) {
			$response->redirect('/twitter/app');
			return;
		}

		$text = $this->form_app_text->getValue();
		if(strlen($text) > 0) {
			$res = $this->execDB('INSERT INTO post VALUES (null,' . $this->escapeString($this->user['id']) . ',"' . $this->escapeString($text) . '",0,' . time() . ')');
			$response->redirect('/twitter/app');
		}
	}
}