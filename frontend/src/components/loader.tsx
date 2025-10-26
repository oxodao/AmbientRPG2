import { Spinner } from './ui/spinner';

export default function LoaderComponent() {
	return (
		<div className='flex flex-col gap-4 m-auto justify-center items-center w-full h-full'>
			<span>Loading...</span>
			<Spinner className='size-8' />
		</div>
	);
}
