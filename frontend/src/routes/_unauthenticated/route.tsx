import { createFileRoute, Outlet, redirect } from '@tanstack/react-router';
import { ThemeSelectorButton } from '@/components/theme-selector-button';
import { useAuthStore } from '@/stores/auth';

export const Route = createFileRoute('/_unauthenticated')({
	component: RouteComponent,
	beforeLoad: async () => {
		const { token, tokenUser } = useAuthStore.getState();

		if (token && tokenUser) {
			throw redirect({ to: '/', reloadDocument: true });
		}
	},
});

function RouteComponent() {
	return (
		<div className='flex flex-row w-full h-full items-center justify-center'>
			<Outlet />

			<div className='absolute top-4 right-4'>
				<ThemeSelectorButton />
			</div>
		</div>
	);
}
