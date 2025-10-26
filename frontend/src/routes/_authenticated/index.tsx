import { createFileRoute, Link } from '@tanstack/react-router';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { useAuthStore } from '@/stores/auth';

export const Route = createFileRoute('/_authenticated/')({
	component: RouteComponent,
	loader: async ({context}) => {
		const tokenUser = useAuthStore.getState().tokenUser;
		if (!tokenUser) {
			throw new Error('User not authenticated');
		}

		// @TODO: Use tanstack query
		// return await sdk.campaigns.getCollection();
	}
});

function RouteComponent() {
	useTranslatedTitle('home.title');

	return (
		<div className='p-4 flex flex-col gap-4'>
			Welcome to your new project's home page
			<Link to='/me'>Go to my profile</Link>
		</div>
	);
}
