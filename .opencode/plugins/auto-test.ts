import type { Plugin } from "@opencode-ai/plugin"

const PHP_EXT = ".php"

function findTestFile(sourcePath: string): string | null {
  if (!sourcePath.endsWith(PHP_EXT)) {
    return null
  }
  if (sourcePath.includes("/tests/") || sourcePath.includes("\\tests\\")) {
    return sourcePath
  }
  const rel = sourcePath.replace(/^.*\/app\//, "").replace(/\//g, "/")
  const base = rel.replace(PHP_EXT, "")
  const candidates = [`tests/Unit/${base}Test${PHP_EXT}`, `tests/Feature/${base}Test${PHP_EXT}`]
  for (const candidate of candidates) {
    const file = Bun.file(candidate)
    if (file.size > 0) {
      return candidate
    }
  }
  return null
}

export const AutoTestPlugin: Plugin = async ({ client, $ }) => {
  return {
    "file.edited": async ({ filePath }) => {
      const testFile = findTestFile(filePath)
      if (!testFile) {
        return
      }

      await client.app.log({
        body: {
          service: "auto-test",
          level: "info",
          message: `Running tests for: ${testFile}`,
        },
      })

      const result = await $`php artisan test --compact --stop-on-failure ${testFile}`.quiet()

      if (result.exitCode === 0) {
        await client.app.log({
          body: {
            service: "auto-test",
            level: "info",
            message: `Tests passed: ${testFile}`,
          },
        })
      } else {
        await client.app.log({
          body: {
            service: "auto-test",
            level: "error",
            message: `Tests failed: ${testFile}\n${result.stderr.toString().slice(0, 1000)}`,
          },
        })
      }
    },
  }
}
