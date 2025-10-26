import { createFileRoute } from '@tanstack/react-router';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import { sdk } from '@/api';
import MercureTest from '@/components/account/mercure_test';
import ProfileEditorCard from '@/components/account/profile_editor_card';
import { Button } from '@/components/ui/button';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { useAuthStore } from '@/stores/auth';

export const Route = createFileRoute('/_authenticated/me')({
	component: RouteComponent,
	loader: async ({ context }) => {
		const user = useAuthStore.getState().tokenUser;
		if (!user) {
			throw new Error('User not authenticated');
		}

		return context.queryClient.fetchQuery({
			queryKey: ['users', user.id],
			queryFn: async () => await sdk.users.get(user.id),
		});
	},
});

function RouteComponent() {
	const data = Route.useLoaderData();
	const { t } = useTranslation();
	const { setToken, token, refreshToken } = useAuthStore();

	useTranslatedTitle('account.my_profile');

	const copyToClipboard = async (text: string) => {
		try {
			await navigator.clipboard.writeText(text);
			toast.success('Copied to clipboard!')
		} catch (err) {
			toast.error('Failed to copy to clipboard.', { description: (err as Error).message });
		}
	};

	return (
		<div className='flex flex-col gap-4 m-auto max-w-120'>
			<ProfileEditorCard user={data} isProfilePage />
			<MercureTest />

			<Button variant='outline' onClick={() => copyToClipboard(token || '')}>
				Copy token
			</Button>

			<Button variant='outline' onClick={() => copyToClipboard(refreshToken || '')}>
				Copy RefreshToken
			</Button>

			<Button variant='destructive' onClick={() => setToken(null, null)}>
				{t('generic.logout')}
			</Button>
		</div>
	);
}
