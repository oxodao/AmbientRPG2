import { createFileRoute } from '@tanstack/react-router';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import { sdk } from '@/api';
import { HttpError } from '@/api/http_error';
import Form from '@/components/rhf/form';
import { RhfInput } from '@/components/rhf/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export const Route = createFileRoute('/_unauthenticated/forgotten-password')({
	component: RouteComponent,
});

type ForgottenPasswordFormData = {
	email: string;
};

function RouteComponent() {
	const { t } = useTranslation();

	const [mailSent, setMailSent] = useState(false);

	const form = useForm<ForgottenPasswordFormData>({
		defaultValues: {
			email: '',
		},
	});

	const onSubmit = async (data: ForgottenPasswordFormData) => {
		try {
			await sdk.auth.requestPasswordReset(data.email);
			setMailSent(true);
		} catch (err) {
			console.log(err);
			if (err instanceof HttpError) {
				toast.error(t(`errors.${err.status}.title`), { description: t(`errors.${err.status}.message`) });

				return;
			}

			toast.error(t('errors.generic.title'), { description: t('errors.generic.message') });
		}
	};

	return (
		<Card className='w-full max-w-md'>
			<CardHeader>
				<CardTitle>{t('reset_password.title')}</CardTitle>
			</CardHeader>

			<CardContent className='flex flex-col gap-4'>
				{mailSent ? (
					<p className='text-green-500'>{t('reset_password.mail_sent')}</p>
				) : (
					<>
						<p>{t('reset_password.request_text')}</p>

						<Form
							methods={form}
							onSubmit={onSubmit}
							actionButtons={<Button disabled={!form.formState.isValid}>{t('reset_password.request_button')}</Button>}
						>
							<RhfInput name='email' label={t('generic.email')} type='email' />
						</Form>
					</>
				)}
			</CardContent>
		</Card>
	);
}
