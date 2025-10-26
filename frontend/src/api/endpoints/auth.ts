import type { AuthenticationResponse, TokenUser } from '@/types/auth';
import type { SDK } from '..';
import { HttpError, HttpServerError } from '../http_error';

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
	private sdk: SDK;

	constructor(sdk: SDK) {
		this.sdk = sdk;
	}

	public async login(username: string, password: string): Promise<AuthenticationResponse> {
		try {
			const response = await fetch(`/api/login`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ username, password }),
			});

			if (!response.ok) {
				if (response.status > 499) {
					throw new HttpServerError({
						message: `errors.5xx.title`,
						submessage: `errors.5xx.message`,
						status: response.status,
					});
				}

				if (response.status === 401) {
					throw new HttpError({
						message: `errors.401.title`,
						submessage: `errors.401.message`,
						status: response.status,
					});
				}
			}

			return await response.json();
		} catch (err) {
			if (err instanceof HttpError) throw err;

			throw new HttpError({
				message: `Network error on login`,
				status: 0,
			});
		}
	}

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

	public async checkForgottenPasswordCodeValidity(code: string): Promise<boolean> {
		try {
			await this.sdk.get(`/api/forgotten_password_requests/${code}`);

			return true;
		} catch {
			return false;
		}
	}

	public async requestPasswordReset(email: string): Promise<void> {
		await this.sdk.post(
			`/api/forgotten_password_requests`,
			{
				email,
			},
			{},
			false,
		);
	}

	public async resetPassword(code: string, newPassword: string): Promise<void> {
		await this.sdk.post(`/api/forgotten_password_requests/${code}`, { newPassword }, {}, false);
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
