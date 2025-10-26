import type { QueryClient } from '@tanstack/react-query';
import { createRootRouteWithContext, Outlet } from '@tanstack/react-router';
import { TanStackRouterDevtools } from '@tanstack/react-router-devtools';
import { FuzzyErrorComponent, NotFoundErrorComponent } from '@/components/error';
import LoaderComponent from '@/components/loader';

export type RouterContext = {
	queryClient: QueryClient;
};

export const Route = createRootRouteWithContext<RouterContext>()({
	component: RootComponent,
	pendingComponent: () => <LoaderComponent />,
	notFoundComponent: NotFoundErrorComponent,
	errorComponent: ({ error }) => <FuzzyErrorComponent error={error} />,
});

function RootComponent() {
	return (
		<>
			<Outlet />
			<TanStackRouterDevtools />
		</>
	);
}
