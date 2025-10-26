import { useAuthStore } from '@/stores/auth';
import type { Collection, EnumValue } from '../types';
import { Auth } from './endpoints/auth';
import Campaigns from './endpoints/campaigns';
import Users from './endpoints/users';
import { HttpError, HttpServerError, makeError, ProblemDetailsError } from './http_error';
import { ValidationError } from './violations_error';

export class SDK {
	public auth: Auth;
	public users: Users;
	public campaigns: Campaigns;

	constructor() {
		this.auth = new Auth();
		this.users = new Users(this);
		this.campaigns = new Campaigns(this);
	}

	public async get<T>(endpoint: string, init?: RequestInit): Promise<T> {
		const resp = await this.customFetch(endpoint, { method: 'GET', ...init });

		return await resp.json();
	}

	public async post<T>(endpoint: string, body: any, init?: RequestInit, parse: boolean = true): Promise<T | null> {
		const resp = await this.customFetch(endpoint, {
			method: 'POST',
			body: JSON.stringify(body),
			...init,
		});

		if (!parse) {
			return null;
		}

		return await resp.json();
	}

	public async patch<T>(endpoint: string, body: any, init?: RequestInit): Promise<T> {
		const resp = await this.customFetch(endpoint, {
			method: 'PATCH',
			body: JSON.stringify(body),
			...init,
		});

		return await resp.json();
	}

	public async delete(endpoint: string, init?: RequestInit) {
		await this.customFetch(endpoint, { method: 'DELETE', ...init });
	}

	public async getEnum(name: string): Promise<Collection<EnumValue>> {
		return this.get(`/api/${name}`);
	}

	public async customFetch(input: RequestInfo, init: RequestInit = {}) {
		const { token, tokenUser, setToken } = useAuthStore.getState();

		const headers = new Headers(init.headers || {});
		if (token) {
			headers.set('Authorization', `Bearer ${token}`);
		}

		if (!headers.has('Accept-Language')) {
			headers.set('Accept-Language', tokenUser?.language || 'en_US');
		}

		if (!headers.has('Content-Type') && init.method && !(init.body instanceof FormData)) {
			const method = init.method.toUpperCase();

			if (method === 'PATCH') {
				headers.set('Content-Type', 'application/merge-patch+json');
			} else if (['POST', 'PUT'].includes(method)) {
				headers.set('Content-Type', 'application/ld+json');
			}
		}

		try {
			const response = await fetch(input, {
				...init,
				headers,
			});

			if (!response.ok) {
				if (response.status > 499) {
					throw new HttpServerError({
						message: `errors.5xx.title`,
						submessage: `errors.5xx.message`,
						status: response.status,
					});
				}

				// At some point we might want to try to refresh the token
				// but for now just disconnect the user
				if (response.status === 401) {
					setToken(null, null);

					throw new HttpError({
						message: `errors.401.title`,
						submessage: `errors.401.message`,
						status: response.status,
					});
				}

				if ([403, 404].includes(response.status)) {
					throw makeError(response.status);
				}

				let errorBody: any = null;

				try {
					errorBody = await response.clone().json();

					if (response.status === 422) {
						throw new ValidationError(errorBody.violations || []);
					}

					// RFC7807 compilant error
					if (errorBody.title && errorBody.detail) {
						throw new ProblemDetailsError(errorBody);
					}
				} catch (err) {
					if (err instanceof ValidationError || err instanceof ProblemDetailsError) {
						throw err;
					}

					try {
						errorBody = await response.clone().text();
					} catch {
						// ignore
					}
				}

				throw new HttpError({
					message: `HTTP error ${response.status} on ${response.url}`,
					status: response.status,
					body: errorBody,
				});
			}

			return response;
		} catch (err) {
			if (err instanceof HttpError) throw err;

			throw new HttpError({
				message: `Network error on ${typeof input === 'string' ? input : input.toString()}`,
				status: 0,
			});
		}
	}
}

export const sdk = new SDK();
