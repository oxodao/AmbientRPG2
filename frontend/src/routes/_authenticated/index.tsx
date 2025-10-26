import { createFileRoute, Link } from '@tanstack/react-router';

export const Route = createFileRoute('/_authenticated/')({
	component: RouteComponent,
});

function RouteComponent() {
	return (
		<div className='p-4 flex flex-col gap-4'>
			Welcome to your new project's home page
			<Link to='/me'>Go to my profile</Link>
		</div>
	);
}
