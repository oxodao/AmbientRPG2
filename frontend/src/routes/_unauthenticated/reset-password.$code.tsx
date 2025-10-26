import { createFileRoute, Link } from '@tanstack/react-router';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { sdk } from '@/api';
import ResetPasswordForm from '@/components/reset_password/form';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';

export const Route = createFileRoute('/_unauthenticated/reset-password/$code')({
	component: RouteComponent,
	loader: async ({ params }) => {
		const isValid = await sdk.auth.checkForgottenPasswordCodeValidity(params.code);

		return { code: isValid ? params.code : null };
	},
});

function RouteComponent() {
	const data = Route.useLoaderData();
	const { t } = useTranslation();

	const [passwordUpdated, setPasswordUpdated] = useState(false);

	useTranslatedTitle('reset_password.title');

	return (
		<Card className='w-full max-w-100'>
			<CardHeader>
				<CardTitle>{t('reset_password.title')}</CardTitle>

				<CardContent className='flex flex-col p-5'>
					{!data.code && <p className='text-red-500'>{t('reset_password.invalid_code')}</p>}

					{!passwordUpdated && data.code && (
						<ResetPasswordForm code={data.code} onSuccess={() => setPasswordUpdated(true)} />
					)}

					{passwordUpdated && (
						<>
							<p className='text-green-500'>{t('reset_password.password_updated')}</p>
							<Link to='/login' className='text-blue-300 underline mt-5 text-center'>
								{t('login.title')}
							</Link>
						</>
					)}
				</CardContent>
			</CardHeader>
		</Card>
	);
}
