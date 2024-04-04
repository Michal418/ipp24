import subprocess
import pathlib
import re

RED = '\033[91m' #]
BLUE = '\033[94m' #]
RESET = '\033[0m' #]

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

        with open(test + '.rc', 'r') as file:
            return_code = int(file.read())

        if pathlib.Path(test + '.out').exists():
            expected_output = pathlib.Path(test + '.out').read_text(encoding='utf-8')
        else:
            expected_output = None

        if pathlib.Path(test + '.in').exists():
            input = pathlib.Path(test + '.in').read_text()
        else:
            input = ''

        print(*cmd)
        p = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=None, input=input.encode())

        code_passed = p.returncode == return_code
        output_passed = expected_output is None or p.stdout.decode() == expected_output

        if code_passed and output_passed:
            print(f'{BLUE}Test {path.name} passed{RESET}')
            continue

        if not code_passed:
            print(f'{RED}Test {path.name} failed with return code {p.returncode} instead of {return_code}{RESET}')

        if not output_passed:
            out = p.stdout.decode().replace('\n', '\\n')
            expect = expected_output.replace('\n', '\\n') if expected_output is not None else ''
            print(f'{RED}Test {path.name} failed with output "{RESET}{out}{RED}" instead of "{RESET}{expect}{RED}"{RESET}')

if __name__ == '__main__':
    main()
