import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import { sdk } from '@/api';
import { ValidationError } from '@/api/violations_error';
import Form from '../rhf/form';
import { RhfPasswordInput } from '../rhf/input';
import { Button } from '../ui/button';

type Props = {
	code: string;
	onSuccess: () => void;
};

type PasswordReset = {
	newPassword: string;
	confirmNewPassword: string;
};

export default function ResetPasswordForm({ code, onSuccess }: Props) {
	const { t } = useTranslation();

	const form = useForm<PasswordReset>({
		defaultValues: {
			newPassword: '',
			confirmNewPassword: '',
		},
		mode: 'onChange',
	});

	const watchedFields = form.watch(['newPassword', 'confirmNewPassword']);
	const [newPassword] = watchedFields;

	const validateConfirmPassword = (value: string) => {
		if (!newPassword || !value) {
			return true;
		}

		if (value !== newPassword) {
			return t('reset_password.passwords_not_matching');
		}

		return true;
	};

	const onSubmit = async (data: PasswordReset) => {
		try {
			await sdk.auth.resetPassword(code, data.newPassword);
			onSuccess();
		} catch (err) {
			if (err instanceof ValidationError) {
				err.applyToReactHookForm(form.setError);

				return;
			}

			toast.error(t('errors.generic.title'), { description: t('errors.generic.message') });

			form.reset();
		}
	};

	return (
		<Form
			methods={form}
			onSubmit={onSubmit}
			actionButtons={
				<Button disabled={!form.formState.isValid} className='mt-4'>
					{t('generic.save')}
				</Button>
			}
			fieldsetClassname='flex flex-col gap-2'
		>
			<RhfPasswordInput name='newPassword' label={t('reset_password.new_password')} rules={{ required: true }} />

			<RhfPasswordInput
				name='confirmNewPassword'
				label={t('reset_password.confirm_password')}
				rules={{
					required: true,
					validate: validateConfirmPassword,
				}}
			/>
		</Form>
	);
}
