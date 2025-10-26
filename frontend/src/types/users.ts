export type User = {
	id: number;
	username: string;
	email: string;
	language: string;
	roles: string[];

	oauthLogin: boolean;
};

export type EditUser = Omit<User, 'id' | 'username' | 'roles'>;
