import { useQuery } from '@tanstack/react-query';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { sdk } from '@/api';
import { useMediaQuery } from '@/hooks/use-media-query';
import { useAuthStore } from '@/stores/auth';
import type { EnumValue } from '@/types';
import { Button } from '../ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '../ui/command';
import { Drawer, DrawerContent, DrawerTrigger } from '../ui/drawer';
import { Popover, PopoverContent, PopoverTrigger } from '../ui/popover';
import { Spinner } from '../ui/spinner';

type Props = {
	enumName: string;
	value: string | null;
	onChange: (val: string | null) => void;
	disabled?: boolean;
	id?: string;
	className?: string;
	'aria-invalid'?: boolean;
	'aria-describedby'?: string;
};

type EnumValueListProps = {
	setOpen: (open: boolean) => void;
	setSelectedValue: (val: EnumValue | null) => void;
	values: EnumValue[];
};

function EnumValueList({ setOpen, setSelectedValue: setSelectedStatus, values }: EnumValueListProps) {
	const { t } = useTranslation();

	return (
		<Command>
			<CommandInput placeholder={t('generic.search')} />
			<CommandList>
				<CommandEmpty>{t('generic.no_results')}</CommandEmpty>
				<CommandGroup>
					{values.map(ev => (
						<CommandItem
							key={ev.value}
							value={ev.value}
							onSelect={(value: string) => {
								setSelectedStatus(values.find(x => x.value === value) || null);
								setOpen(false);
							}}
						>
							{ev.label}
						</CommandItem>
					))}
				</CommandGroup>
			</CommandList>
		</Command>
	);
}

export default function EnumSelect({ enumName, value, onChange, className, ...field }: Props) {
	const [open, setOpen] = useState(false);
	const isDesktop = useMediaQuery('(min-width: 768px)');
	const { t } = useTranslation();

	const { tokenUser } = useAuthStore.getState();

	const values = useQuery<EnumValue[]>({
		queryKey: ['enum_values', enumName, tokenUser?.language],
		queryFn: async () => (await sdk.getEnum(enumName)).member,
		staleTime: 24 * 60 * 60 * 1000,
		gcTime: 7 * 24 * 60 * 60 * 1000,
	});

	if (values.isPending) {
		return <Spinner scale={4} />;
	}

	if (isDesktop) {
		return (
			<Popover open={open} onOpenChange={setOpen}>
				<PopoverTrigger asChild>
					<Button variant='outline' className={`w-[150px] justify-start ${className}`} {...field}>
						{value ? values.data?.find(x => x['@id'] === value)?.label : <>+ {t('generic.select_an_option')}</>}
					</Button>
				</PopoverTrigger>
				<PopoverContent className='w-[200px] p-0' align='start'>
					{values.data && (
						<EnumValueList
							setOpen={setOpen}
							setSelectedValue={x => onChange(x?.['@id'] || null)}
							values={values.data}
						/>
					)}
				</PopoverContent>
			</Popover>
		);
	}

	return (
		<Drawer open={open} onOpenChange={setOpen}>
			<DrawerTrigger asChild>
				<Button variant='outline' className={`w-[150px] justify-start ${className}`} {...field}>
					{value ? values.data?.find(x => x['@id'] === value)?.label : <>+ {t('generic.select_an_option')}</>}
				</Button>
			</DrawerTrigger>
			<DrawerContent>
				<div className='mt-4 border-t'>
					{values.data && (
						<EnumValueList
							setOpen={setOpen}
							setSelectedValue={x => onChange(x?.['@id'] || null)}
							values={values.data}
						/>
					)}
				</div>
			</DrawerContent>
		</Drawer>
	);
}
