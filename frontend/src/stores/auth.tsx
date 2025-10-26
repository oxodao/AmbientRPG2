import Cookies from 'js-cookie';
import { create } from 'zustand';
import { sdk } from '@/api';
import type { TokenUser } from '@/types/auth';

export type StoreType = {
	token: string | null;
	refreshToken: string | null;
	tokenUser: TokenUser | null;
	setToken: (token: string | null, refreshToken: string | null) => void;
	doRefresh: () => Promise<void>;
	isGranted: (role: string) => boolean;
};

export const useAuthStore = create<StoreType>()((set, get) => ({
	token: localStorage.getItem('token') || null,
	refreshToken: localStorage.getItem('refreshToken') || null,
	tokenUser: sdk.auth.decodeToken(localStorage.getItem('token')),
	setToken: (token: string | null, refreshToken: string | null) =>
		set(() => {
			if (!token || !refreshToken) {
				localStorage.removeItem('token');
				localStorage.removeItem('refreshToken');
				Cookies.remove('mercureAuthorization');

				return {
					token: null,
					refreshToken: null,
					tokenUser: null,
				};
			}

			localStorage.setItem('token', token);
			localStorage.setItem('refreshToken', refreshToken);
			Cookies.set('mercureAuthorization', token);

			return {
				token,
				refreshToken,
				tokenUser: sdk.auth.decodeToken(token),
			};
		}),
	doRefresh: async () => {
		const resp = await sdk.auth.refresh(get().refreshToken);

		get().setToken(resp?.token ?? null, resp?.refresh_token ?? null);
	},
	isGranted: (role: string) => {
		const tokenUser = get().tokenUser;
		if (!tokenUser) {
			return false;
		}

		return tokenUser.roles.includes(role) || tokenUser.roles.includes('ROLE_ADMIN');
	},
	isDm: () => {
		const tokenUser = get().tokenUser;
		if (!tokenUser) {
			return false;
		}

		return tokenUser.roles.includes('ROLE_DM') || tokenUser.roles.includes('ROLE_ADMIN');
	},
}));
