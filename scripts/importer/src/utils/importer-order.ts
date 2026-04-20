export interface DependentConfig {
  key: string;
  dependencies?: string[];
}

/**
 * Order importer configs so declared dependencies run before their dependents.
 * Dependencies outside the selected config set are ignored to preserve CLI
 * selection semantics for --only, --start-at, and --stop-at.
 */
export function orderConfigsByDependencies<T extends DependentConfig>(configs: T[]): T[] {
  const configMap = new Map(configs.map((config) => [config.key, config]));
  const selectedKeys = new Set(configs.map((config) => config.key));
  const inDegree = new Map(configs.map((config) => [config.key, 0]));
  const dependents = new Map<string, string[]>();

  for (const config of configs) {
    for (const dependency of config.dependencies ?? []) {
      if (!selectedKeys.has(dependency)) {
        continue;
      }

      inDegree.set(config.key, (inDegree.get(config.key) ?? 0) + 1);
      const dependencyDependents = dependents.get(dependency) ?? [];
      dependencyDependents.push(config.key);
      dependents.set(dependency, dependencyDependents);
    }
  }

  const readyQueue = configs
    .filter((config) => (inDegree.get(config.key) ?? 0) === 0)
    .map((config) => config.key);
  const orderedKeys: string[] = [];

  while (readyQueue.length > 0) {
    const key = readyQueue.shift();
    if (!key) {
      break;
    }

    orderedKeys.push(key);

    for (const dependentKey of dependents.get(key) ?? []) {
      const nextInDegree = (inDegree.get(dependentKey) ?? 0) - 1;
      inDegree.set(dependentKey, nextInDegree);
      if (nextInDegree === 0) {
        readyQueue.push(dependentKey);
      }
    }
  }

  if (orderedKeys.length !== configs.length) {
    const unresolved = configs
      .filter((config) => !orderedKeys.includes(config.key))
      .map((config) => config.key)
      .join(', ');
    throw new Error(`Circular importer dependencies detected: ${unresolved}`);
  }

  return orderedKeys.map((key) => {
    const config = configMap.get(key);
    if (!config) {
      throw new Error(`Importer config not found for key: ${key}`);
    }
    return config;
  });
}
