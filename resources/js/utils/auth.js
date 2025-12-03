export function getUser() {
  const user = localStorage.getItem('user');
  return user ? JSON.parse(user) : null;
}

export function isAdmin() {
  const user = getUser();
  return user?.role === 'admin';
}

export function hasRole(role) {
  const user = getUser();
  return user?.role === role;
}
