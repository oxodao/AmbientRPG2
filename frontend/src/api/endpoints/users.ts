import type { EditUser, User } from '@/types/users';
import type { SDK } from '..';

export default class Users {
	private sdk: SDK;

	constructor(sdk: SDK) {
		this.sdk = sdk;
	}

	public async get(id: number): Promise<User> {
		return this.sdk.get<User>(`/api/users/${id}`);
	}

	public async update(id: number, data: EditUser): Promise<User> {
		return this.sdk.patch<User>(`/api/users/${id}`, data);
	}
}
