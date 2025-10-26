import { useTranslation } from 'react-i18next';
import type { User } from '@/types/users';
import EnumSelect from '../generic/enum_select';
import RhfEnumSelect from '../rhf/enum_select';
import { DisplayInput } from '../rhf/input';
import { ThemeSelectorButton } from '../theme-selector-button';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { FieldGroup, FieldLabel } from '../ui/field';

type Props = {
	user: User;
	isProfilePage?: boolean;
};

export default function ProfileEditorCard({ user, isProfilePage }: Props) {
	const { t } = useTranslation();

	return (
		<Card>
			<CardHeader className='flex flex-row items-center justify-between'>
				<CardTitle>{t('account.my_profile')}</CardTitle>
				{isProfilePage && <ThemeSelectorButton />}
			</CardHeader>
			<CardContent className='flex flex-col gap-2'>
				<DisplayInput label={t('generic.username')} value={user.username} />
				<DisplayInput label={t('generic.email')} value={user.email} />

				<FieldGroup className='w-full flex flex-col gap-1'>
					<FieldLabel>{t('generic.language')}:</FieldLabel>
					<EnumSelect
						enumName='languages'
						value={user.language}
						className='w-full flex justify-center'
						disabled
					/>
				</FieldGroup>

				{user.roles.length > 0 && (
					<p>
						{t('account.roles')}: {user.roles.join(', ')}
					</p>
				)}

				{<p className='text-center text-muted-foreground'>{t('account.locked_oauth')}</p>}
			</CardContent>
		</Card>
	);
}
