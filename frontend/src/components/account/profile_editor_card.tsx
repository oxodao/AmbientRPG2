import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import { sdk } from '@/api';
import { ValidationError } from '@/api/violations_error';
import { useAuthStore } from '@/stores/auth';
import type { EditUser, User } from '@/types/users';
import RhfEnumSelect from '../rhf/enum_select';
import Form from '../rhf/form';
import { DisplayInput, RhfInput } from '../rhf/input';
import { ThemeSelectorButton } from '../theme-selector-button';
import { Button } from '../ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';

type Props = {
	user: User;
	isProfilePage?: boolean;
};

export default function ProfileEditorCard({ user, isProfilePage }: Props) {
	const { t } = useTranslation();
	const { doRefresh } = useAuthStore.getState();

	const form = useForm<EditUser>({
		defaultValues: {
			email: user.email,
			language: user.language,
		},
	});

	/**
	 * Do NOT use tanstack query's mutation.
	 * It sucks for this purpose as it bypasses
	 * the magic to auto-parse validation errors in Form component.
	 *
	 * tl;dr: useMutation handles exception outside of the Form component, call your sdk directly
	 */
	const onSubmit = async (editedUser: EditUser) => {
		try {
			const newUser = await sdk.users.update(user.id, editedUser);

			/**
			 * We do refresh the token in case the user
			 * changes the language since that's where its stored.
			 */

			await doRefresh();

			toast.success(t('generic.changes_saved'));

			form.reset(newUser);
		} catch (err) {
			if (err instanceof ValidationError) {
				throw err;
			}

			console.error('Failed to update user profile:', err);

			toast.error(t('errors.generic.title'), { description: t('errors.generic.message') });
		}
	};

	return (
		<Card>
			<CardHeader className='flex flex-row items-center justify-between'>
				<CardTitle>{t('account.my_profile')}</CardTitle>
				{isProfilePage && <ThemeSelectorButton />}
			</CardHeader>
			<CardContent>
				<Form
					methods={form}
					onSubmit={onSubmit}
					actionButtons={
						!user.oauthLogin && (
							<Button type='submit' disabled={form.formState.isSubmitting || !form.formState.isDirty}>
								{t('generic.save')}
							</Button>
						)
					}
					fieldsetClassname='flex flex-col gap-2'
					globallyDisabled={user.oauthLogin}
				>
					<DisplayInput label={t('generic.username')} value={user.username} />

					<RhfInput name='email' label={t('generic.email')} />

					<RhfEnumSelect
						enumName='languages'
						name='language'
						label={t('generic.language')}
						className='w-full flex justify-center'
					/>

					{user.roles.length > 0 && (
						<p>
							{t('account.roles')}: {user.roles.join(', ')}
						</p>
					)}
				</Form>

				{user.oauthLogin && <p className='text-center text-muted-foreground'>{t('account.locked_oauth')}</p>}
			</CardContent>
		</Card>
	);
}
