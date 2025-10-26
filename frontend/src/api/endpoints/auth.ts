import type { AuthenticationResponse, TokenUser } from '@/types/auth';
import { HttpError } from '../http_error';

/**
 * fuck the browsers
 */
function decodeBase64Url(str: string): string {
	let b64 = str.replace(/-/g, '+').replace(/_/g, '/');
	while (b64.length % 4) b64 += '=';
	return decodeURIComponent(
		Array.prototype.map.call(atob(b64), (c: string) => `%${(`00${c.charCodeAt(0).toString(16)}`).slice(-2)}`).join(''),
	);
}

export class Auth {
	public async loginOAuth(code: string): Promise<AuthenticationResponse> {
		const resp = await fetch(`/api/login_oauth`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ code }),
		});

		if (!resp.ok) {
			throw new HttpError({
				message: `errors.oauth_login_failed.title`,
				submessage: `errors.oauth_login_failed.message`,
				status: resp.status,
			});
		}

		return await resp.json();
	}

	public async refresh(refreshToken: string | null): Promise<AuthenticationResponse | null> {
		if (!refreshToken) {
			return null;
		}

		const resp = await fetch(`/api/login_refresh`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ refresh_token: refreshToken }),
		});

		if (!resp.ok) {
			return null;
		}

		try {
			return await resp.json();
		} catch {
			return null;
		}
	}

	public decodeToken(token?: string | null): TokenUser | null {
		if (!token) {
			return null;
		}

		const [, payload] = token.split('.');
		const json = decodeBase64Url(payload);
		return JSON.parse(json) as TokenUser;
	}
}
