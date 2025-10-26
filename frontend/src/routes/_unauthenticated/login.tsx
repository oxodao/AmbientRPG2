import { createFileRoute, useNavigate } from '@tanstack/react-router';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import useTranslatedTitle from '../../hooks/useTranslatedTitle';

export const Route = createFileRoute('/_unauthenticated/login')({
	component: RouteComponent,
});

function RouteComponent() {
	const { t } = useTranslation();
	const navigate = useNavigate();

	const [roomId, setRoomId] = useState('');

	useTranslatedTitle('login.title');

	return (
		<Card className='w-full sm:w-80 max-w-80 m-4'>
			<CardHeader className='text-center'>
				<CardTitle>{t('login.title')}</CardTitle>
			</CardHeader>

			<CardContent className='flex flex-col gap-4'>
				<div className='flex flex-row gap-4'>
					<Input value={roomId} onChange={x => setRoomId(x.target.value)} />
					<Button disabled={roomId.length === 0} onClick={() => navigate({ to: '/rooms/$roomId', params: { roomId } })}>
						{t('login.join_room')}
					</Button>
				</div>

				<Button variant='outline' asChild>
					<a href='/api/login_oauth'>{t('login.oauth_login')}</a>
				</Button>
			</CardContent>
		</Card>
	);
}
