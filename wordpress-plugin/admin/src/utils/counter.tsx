export function createCounter(prefix: string) {
  let count = 0;
  return () => `${prefix}__${count++}`;
}
