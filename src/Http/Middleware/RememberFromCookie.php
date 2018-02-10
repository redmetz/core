<?php

/*
 * This file is part of Flarum.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flarum\Http\Middleware;

use Flarum\Http\AccessToken;
use Flarum\Http\CookieFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Stratigility\MiddlewareInterface;

class RememberFromCookie implements MiddlewareInterface
{
    /**
     * @var CookieFactory
     */
    protected $cookie;

    /**
     * @param CookieFactory $cookie
     */
    public function __construct(CookieFactory $cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $id = array_get($request->getCookieParams(), $this->cookie->getName('remember'));

        if ($id) {
            $token = AccessToken::find($id);

            if ($token) {
                $token->touch();

                /** @var \Illuminate\Contracts\Session\Session $session */
                $session = $request->getAttribute('session');
                $session->put('user_id', $token->user_id);
            }
        }

        return $out ? $out($request, $response) : $response;
    }
}
