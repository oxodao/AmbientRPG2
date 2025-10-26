import { createFileRoute, useNavigate } from '@tanstack/react-router';
import { useEffect } from 'react';
import { sdk } from '@/api';
import LoaderComponent from '@/components/loader';
import { useAuthStore } from '@/stores/auth';

export const Route = createFileRoute('/_unauthenticated/oauth-callback')({
	component: RouteComponent,
	validateSearch: search => {
		if (typeof search.code !== 'string' || !search.code) {
			throw new Error('Missing code parameter');
		}

		return { code: search.code as string };
	},
});

function RouteComponent() {
	const { setToken } = useAuthStore();
	const navigate = useNavigate();
	const {
		// state,
		// session_state,
		// iss,
		code,
	} = Route.useSearch();

	useEffect(() => {
		(async () => {
			try {
				const resp = await sdk.auth.loginOAuth(code);
				setToken(resp.token, resp.refresh_token);
				navigate({ to: '/' });
			} catch (e) {
				console.error('OAuth login failed', e);
				// @TODO: Show error to user properly
			}
		})();
	}, [code, setToken, navigate]);

	return <LoaderComponent />;
}
