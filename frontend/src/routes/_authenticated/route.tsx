import { createFileRoute, Outlet, redirect, useNavigate } from '@tanstack/react-router';
import { useEffect } from 'react';
import MercureProvider from '@/hooks/useMercure';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { setI18NLanguage } from '@/lib/utils';
import { useAuthStore } from '@/stores/auth';

export const Route = createFileRoute('/_authenticated')({
	component: RouteComponent,
	beforeLoad: async () => {
		const { token, tokenUser, doRefresh } = useAuthStore.getState();

		if (!token || !tokenUser) {
			throw redirect({ to: '/login', reloadDocument: true });
		}

		setI18NLanguage(tokenUser.language);

		/**
		 * If the token expires in less than 30 seconds,
		 * Refresh it
		 */
		if (Date.now() >= tokenUser.exp * 1000 - 30000) {
			await doRefresh();
		}
	},
});

function RouteComponent() {
	const { tokenUser, refreshToken, doRefresh } = useAuthStore();
	const navigate = useNavigate();
	useTranslatedTitle('home.title');

	/**
	 * Set a timeout to refresh the token
	 * 30 seconds before it expires.
	 *
	 * This ensures that we always have a valid token
	 * or the user gets logged out
	 *
	 * Also update the user language based on its token infos
	 */
	useEffect(() => {
		if (!tokenUser || !refreshToken) {
			navigate({ to: '/login' });
			return;
		}

		setI18NLanguage(tokenUser.language);

		const interval = setTimeout(doRefresh, Math.abs(tokenUser.exp * 1000 - Date.now() - 30000));

		return () => {
			clearTimeout(interval);
		};
	}, [doRefresh, navigate, tokenUser, refreshToken]);

	return (
		<MercureProvider>
			<Outlet />
		</MercureProvider>
	);
}
