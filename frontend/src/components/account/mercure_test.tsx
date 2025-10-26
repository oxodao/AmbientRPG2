import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useMercureListener } from '../../hooks/useMercure';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';

export default function MercureTest() {
	const { t } = useTranslation();
	const [messages, setMessages] = useState<string[]>([]);

	useMercureListener('/messages', data => {
		setMessages(prev => [...prev, JSON.stringify(data)]);
	});

	return (
		<Card>
			<CardHeader>
				<CardTitle>{t('account.mercure.title')}</CardTitle>
			</CardHeader>

			<CardContent className='w-full max-h-60 overflow-y-scroll flex flex-col gap-2'>
				{messages.length === 0 ? (
					<>
						<p className='italic'>{t('account.mercure.no_results')}</p>
						<p className='italic'>{t('account.mercure.run_command')}</p>
						<p className='italic font-bold'>bin/console mercure:submit-test-event [username]</p>
					</>
				) : (
					messages.map((msg, idx) => (
						<p key={idx} className='p-2 border rounded bg-muted w-full max-w-full overflow-scroll min-h-18 text-wrap'>
							[{idx}] {msg}
						</p>
					))
				)}
			</CardContent>
		</Card>
	);
}
