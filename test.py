import subprocess
import pathlib
import re

RED = '\033[91m' #]
BLUE = '\033[94m' #]
LIGHT_BLUE = '\033[96m' #]
RESET = '\033[0m' #]

def escape(s: str) -> str:
    result = ''

    for ch in s:
        if not ch.isprintable():
            result += f'{LIGHT_BLUE}\\{ord(ch)}{RESET}'
        else:
            result += ch

    return result

def main():
    pattern = re.compile(r'(.*?).src$')

    for path in pathlib.Path(r'E:\skola\semester\IPP\project\supplementary-tests\interpret').iterdir():
        if path.is_dir():
            continue

        match = pattern.match(str(path))

        if match is None:
            continue

        print(f' ---------- Running test {path.name} ---------- ')

        test = match.group(1)

        cmd = ['php', r'E:\skola\semester\IPP\project\ipp-core\interpret.php', '--source=' + str(path)]

        if pathlib.Path(test + '.in').exists():
            cmd.append('--input=' + test + '.in')

        with open(test + '.rc', 'r', encoding='utf-8') as file:
            return_code = int(file.read())

        if pathlib.Path(test + '.out').exists():
            expected_output = pathlib.Path(test + '.out').read_text(encoding='utf-8')
        else:
            expected_output = None

        if pathlib.Path(test + '.in').exists():
            input = pathlib.Path(test + '.in').read_bytes()
        else:
            input = ''

        print(*cmd)
        p = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=None, input=input)

        code_passed = p.returncode == return_code
        output_passed = expected_output is None or p.stdout == expected_output.encode()

        if code_passed and output_passed:
            print(f'{BLUE}Test {path.name} passed{RESET}')
            continue

        if not code_passed:
            print(f'{RED}Test {path.name} failed with return code {p.returncode} instead of {return_code}{RESET}')

        if not output_passed:
            out = escape(p.stdout.decode(errors='replace'))
            expect = escape(expected_output) if expected_output is not None else ''
            print(f'{RED}Test {path.name} failed with output "{RESET}{out}{RED}" instead of "{RESET}{expect}{RED}"{RESET}')

if __name__ == '__main__':
    main()
