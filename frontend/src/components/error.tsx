import { Link } from '@tanstack/react-router';
import { AlertCircle, ArrowLeft, Home } from 'lucide-react';
import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';
import { HttpError } from '@/api/http_error';

type Props = {
	status?: number | string;
	title: string;
	message: ReactNode;
};

export const ErrorComponent = ({ status, title, message }: Props) => {
	const { t } = useTranslation();

	return (
		<div className='relative flex flex-col items-center justify-center w-full h-full overflow-hidden bg-gradient-to-b from-[#0a1026] via-[#1a237e] to-[#232946] rounded-lg shadow-xl'>
			{/* Background stars */}
			<div className='absolute inset-0 z-0 pointer-events-none animate-pulse duration-75'>
				<svg width='100%' height='100%' className='w-full h-full'>
					<title>Background stars</title>
					<defs>
						<radialGradient id='star' cx='50%' cy='50%' r='50%'>
							<stop offset='0%' stopColor='#fff' stopOpacity='1' />
							<stop offset='100%' stopColor='#fff' stopOpacity='0' />
						</radialGradient>
					</defs>
					{Array.from({ length: 80 }).map((_, i) => (
						<circle
							key={i}
							cx={`${Math.random() * 100}%`}
							cy={`${Math.random() * 100}%`}
							r={Math.random() * 2.0 + 0.5}
							fill='url(#star)'
							opacity={Math.random() * 0.4 + 0.15}
						/>
					))}
				</svg>
			</div>

			<div className='z-10 mt-10'>
				<div className='bg-white/10 backdrop-blur-md rounded-4xl p-6 shadow-lg border-2 border-blue-300'>
					<AlertCircle className='text-white drop-shadow-lg' size={64} />
				</div>
			</div>

			<div className='z-10 mt-6 text-center px-6 pb-10'>
				<h1 className='text-6xl font-extrabold text-white drop-shadow-lg tracking-widest mb-2'>{status}</h1>
				<h2 className='text-2xl font-semibold text-blue-100 mb-4'>{title}</h2>
				<p className='text-blue-200 mb-8'>{message}</p>
				<div className='flex flex-col sm:flex-row gap-4 justify-center'>
					<button
						type='button'
						className='inline-flex items-center justify-center text-white px-5 py-2.5 rounded-md font-medium cursor-pointer'
						onClick={() => {
							window.history.back();
						}}
					>
						<ArrowLeft size={20} className='mr-2' />
						{t('generic.go_back')}
					</button>
					<Link
						to='/'
						className='inline-flex items-center justify-center text-white px-5 py-2.5 rounded-md font-medium'
					>
						<Home size={20} className='mr-2' />
						{t('home.title')}
					</Link>
				</div>
			</div>
		</div>
	);
};

export const HttpErrorComponent = ({ error }: { error: HttpError }) => {
	const { t } = useTranslation();

	let messageComponent: ReactNode = <></>;
	/**
	 * @TODO: We probably dont want to translate everything
	 * If we have a server error it will already be translated
	 */

	if (error.submessage) {
		messageComponent = t(error.submessage)
			.split('\n')
			.map((line, index) => (
				<span key={index}>
					{line}
					<br />
				</span>
			));
	}

	return <ErrorComponent status={error.status} title={t(error.message)} message={messageComponent} />;
};

export const FuzzyErrorComponent = ({ error }: { error: Error }) => {
	const { t } = useTranslation();

	if (error instanceof HttpError) {
		return <HttpErrorComponent error={error} />;
	}

	return (
		<ErrorComponent
			status={'Mmmh...'}
			title={t('errors.generic.title')}
			message={error.message || t('errors.generic.message')}
		/>
	);
};

export const NotFoundErrorComponent = () => {
	const { t } = useTranslation();

	return (
		<ErrorComponent
			status={404}
			title={t('errors.404.title')}
			message={t('errors.404.message')
				.split('\n')
				.map((line, index) => (
					<span key={index}>
						{line}
						<br />
					</span>
				))}
		/>
	);
};
