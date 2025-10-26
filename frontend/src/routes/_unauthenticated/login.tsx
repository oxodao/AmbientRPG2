import { zodResolver } from '@hookform/resolvers/zod';
import { useMutation } from '@tanstack/react-query';
import { createFileRoute, Link, useNavigate } from '@tanstack/react-router';
import { Lock, User } from 'lucide-react';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import type * as z from 'zod';
import { sdk } from '@/api';
import { HttpError } from '@/api/http_error';
import Form from '@/components/rhf/form';
import { RhfInput, RhfPasswordInput } from '@/components/rhf/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useAuthStore } from '@/stores/auth';
import { AuthenticationRequest } from '@/types/auth';
import useTranslatedTitle from '../../hooks/useTranslatedTitle';

export const Route = createFileRoute('/_unauthenticated/login')({
	component: RouteComponent,
});

function RouteComponent() {
	const { t } = useTranslation();
	const navigate = useNavigate();

	useTranslatedTitle('login.title');

	const form = useForm<z.infer<typeof AuthenticationRequest>>({
		resolver: zodResolver(AuthenticationRequest),
		defaultValues: {
			username: localStorage.getItem('last_username') || '',
			password: '',
		},
	});

	const mutation = useMutation({
		mutationFn: async (data: z.infer<typeof AuthenticationRequest>) =>
			await sdk.auth.login(data.username, data.password),
		onError: (error: any) => {
			if (error instanceof HttpError && error.status === 401) {
				form.setError('root', {
					message: t('login.invalid_credentials'),
				});
				return;
			}

			toast.error(t(error.message), { description: error.submessage ? t(error.submessage) : undefined });
		},
		onSuccess: (resp, data) => {
			localStorage.setItem('last_username', data.username);
			useAuthStore.getState().setToken(resp.token, resp.refresh_token);

			navigate({ to: '/' });
		},
	});

	return (
		<Card className='w-full sm:w-80 max-w-80 m-4'>
			<CardHeader className='text-center'>
				<CardTitle>{t('login.title')}</CardTitle>
			</CardHeader>

			<CardContent className='flex flex-col gap-4'>
				<Form
					className='flex flex-col gap-4'
					fieldsetClassname='flex flex-col gap-3'
					methods={form}
					onSubmit={data => {
						form.setError('root', {});
						mutation.mutate(data);
					}}
					actionButtons={
						<>
							<Link to='/forgotten-password' className='text-center underline text-muted-foreground'>
								{t('reset_password.title')}
							</Link>

							<Button variant='outline' disabled={form.formState.isSubmitting}>
								{t('login.title')}
							</Button>
						</>
					}
				>
					<RhfInput name='username' label={t('generic.username')} icon={<User />} />
					<RhfPasswordInput name='password' label={t('generic.password')} icon={<Lock />} />
				</Form>

				<div className='flex flex-row items-center justify-center'>
					<Button variant='outline' asChild>
						<a href='/api/login_oauth'>{t('login.oauth_login')}</a>
					</Button>
				</div>
			</CardContent>
		</Card>
	);
}
